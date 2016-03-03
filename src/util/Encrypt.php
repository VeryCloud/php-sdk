<?php
/**
 * @file
 * contains demo\util\Encrypt
 * 加密、解密类
 */

namespace demo\util;


class Encrypt {

  /**
   * @var string 密锁串，不能出现重复字符，内有A-Z,a-z,0-9,/,=,+,_,
   */
  private static $lockstream = 'st=lDEFABCNOPyzghi_jQRST-UwxkVWXYZabcdefIJK6/7nopqr89LMmGH012345uv';


  /**
   * 可逆的字符串加密函数
   * @param string $txtStream 待加密的字符串内容
   * @param string $public_key 加密密码
   * @return string 加密后的字符串
   */
  static function encrypt($txtStream, $public_key) {
    //随机找一个数字，并从密锁串中找到一个密锁值
    $lockLen = strlen(self::$lockstream);
    $lockCount = rand(0, $lockLen-1);
    $randomLock = self::$lockstream[$lockCount];
    //结合随机密锁值生成MD5后的密码
    $password = md5($public_key . $randomLock);
    //开始对字符串加密
    $txtStream = base64_encode($txtStream);
    $tmpStream = '';
    $i = 0; $j = 0; $k = 0;
    for($i = 0; $i < strlen($txtStream); $i++) {
      $k = ($k == strlen($password)) ? 0 : $k;
      $j = (strpos(self::$lockstream, $txtStream[$i]) + $lockCount + ord($password[$k])) % ($lockLen);
      $tmpStream .= self::$lockstream[$j];
      $k++;
    }
    return $tmpStream . $randomLock;
  }

  /**
   * 可逆的字符串解密函数
   * @param string $txtStream 待加密的字符串内容
   * @param string $public_key 解密密码
   * @return string 解密后的字符串
   */
  static function decrypt($txtStream, $public_key) {
    $lockLen = strlen(self::$lockstream);
    //获得字符串长度
    $txtLen = strlen($txtStream);
    //截取随机密锁值
    $randomLock = $txtStream[$txtLen - 1];
    //获得随机密码值的位置
    $lockCount = strpos(self::$lockstream, $randomLock);
    //结合随机密锁值生成MD5后的密码
    $password = md5($public_key . $randomLock);
    //开始对字符串解密
    $txtStream = substr($txtStream, 0, $txtLen-1);
    $tmpStream = '';
    $i = 0; $j = 0; $k = 0;
    for($i = 0; $i < strlen($txtStream); $i++) {
      $k = ($k == strlen($password)) ? 0 : $k;
      $j = strpos(self::$lockstream, $txtStream[$i]) - $lockCount - ord($password[$k]);
      while($j < 0) {
        $j = $j + ($lockLen);
      }
      $tmpStream .= self::$lockstream[$j];
      $k++;
    }
    return base64_decode($tmpStream);
  }

}