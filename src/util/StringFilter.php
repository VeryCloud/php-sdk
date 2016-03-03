<?php

/**
 * 过滤字符串
 * @param $text
 * @return string
 */
function check_plain($text) {
  return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}
