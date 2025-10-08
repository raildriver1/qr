<?php
session_destroy();
header('Location: ' . BASE_PATH . '/');
exit;