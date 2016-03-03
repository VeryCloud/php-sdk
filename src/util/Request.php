<?php
/**
 * @file
 * contains demo\util\Request
 * 发送API请求类
 */

namespace demo\util;


class Request
{
  /**
   * @var string 接口地址
   */
  public static $api_url = 'https://api3.verycloud.cn';

  /**
   * 使用curl发送api请求
   * @param string $url 接口地址
   * @param array $data 参数数组
   * @return array
   */
  static function sendRequest($url, $data) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

    $return = curl_exec($ch);
    curl_close($ch);

    if($return) {
      $return = json_decode($return, true);
      return $return;
    }

    return array(
      'code' => 0,
      'message' => '操作失败'
    );
  }
}