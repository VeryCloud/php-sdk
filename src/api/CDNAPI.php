<?php
/**
 * @file
 * contains demo\util\CDNAPI
 * CDN API类
 */

namespace demo\api;

use \demo\util\Request;

require_once('../util/StringFilter.php');

class CDNAPI {
  /**
   * 用户CDN配置
   * @return array
   */
  public function profileDetail() {
    $token = (new Token())->token();
    if(!$token) {
      return array(
        'code' => 0,
        'message' => 'unable to get token'
      );
    }

    $url = Request::$api_url . '/API/cdn/profileDetail';

    $send_data = array(
      'token' => $token
    );

    $return = Request::sendRequest($url, $send_data);
    return $return;
  }

  /**
   * 频道列表
   * @param $data
   * @return array
   */
  public function domainList($data) {
    $token = (new Token())->token();
    if(!$token) {
      return array(
        'code' => 0,
        'message' => 'unable to get token'
      );
    }

    // 分页相关数据
    $page = isset($data['page']) && !empty($data['page']) ? (int) $data['page'] : 1;
    $limit = isset($data['limit']) && !empty($data['limit']) ? (int) $data['limit'] : 30;
    // filter == 1 获取启用了日志下载的频道
    $filter = isset($data['filter']) && !empty($data['filter']) ? (int) $data['filter'] : 0;

    $url = Request::$api_url . '/API/cdn/domainList';

    $send_data = array(
      'token' => $token,
      'page' => $page,
      'limit' => $limit
    );

    if($filter) {
      $send_data['filter'] = $filter;
    }

    //按ID查询
    $id = isset($data['id']) && !empty($data['id']) ? (int) $data['id'] : 0;
    if($id) {
      $send_data['id'] = $id;
    }

    //按加速类型查询 取值范围[cloud(云分发),download(下载),hls(HLS),music(音视),rtmp(RTMP),static(静态)]
    $type = isset($data['type']) && !empty($data['type']) ? check_plain(trim($data['type'])) : '';
    if(!empty($type)) {
      $send_data['type'] = $type;
    }

    //按星标查询
    $mark = isset($data['mark']) ? (int) $data['mark']  : 99;
    if($mark == 1) {
      $send_data['mark'] = $mark;
    }

    $return = Request::sendRequest($url, $send_data);
    return $return;
  }

  /**
   * 新增频道
   * @param $data
   * @return array
   */
  public function domainAdd($data) {
    $token = (new Token())->token();
    if(!$token) {
      return array(
        'code' => 0,
        'message' => 'unable to get token'
      );
    }

    //频道域名
    $name = isset($data['name']) && !empty($data['name']) ? check_plain(trim($data['name'])) : '';
    //加速类型，取值范围[cloud(云分发),download(下载),hls(HLS),music(音视),rtmp(RTMP),static(静态)]
    $type = isset($data['type']) && !empty($data['type']) ? check_plain(trim($data['type'])) : '';
    //源站 如array(1.1.1.1 => 端口)
    $ip = isset($data['ip']) ? $data['ip'] : array();
    //ICP备案号
    $icp = isset($data['icp']) && !empty($data['icp']) ? check_plain(trim($data['icp'])) : '';
    if(empty($name) || empty($type) || empty($icp)) {
      return array(
        'code' => 0,
        'message' => 'name、type and icp is required',
      );
    }
    //备份源站 如array(1.1.1.1 => 端口)
    $originback = isset($data['originback']) ? $data['originback'] : array();
    // 网站类型 如网上商城 个人博客等
    $siteinfo = isset($data['siteinfo']) ? check_plain(trim($data['siteinfo'])) : '';
    // 是否授权 0未授权 1已授权
    $authorize = isset($data['authorize']) ? (int) $data['authorize'] : 0;
    // 缓存策略类型 遵循源站followOrigin 缓存html等静态文件cacheStaic 自定义custom
    $cachetype = isset($data['cachetype']) ? check_plain(trim($data['cachetype'])) : 'followOrigin';
    // 频道缓存策略配置，数组
    /**
     * cacheconf数据格式
     * $cacheconf = array(
     * 'type' => 1缓存 2不缓存 3缓存首页,
     * 'pattern' => $pattern, // 缓存后缀
     * 'ttl' => 5, // 缓存时间
     * 'unit' => 'd', // 缓存时间类型 m分钟 h小时 d天
     * 'path' => '/images/',
     * )
     */
    $cacheconf = isset($data['cacheconf']) ? $data['cacheconf'] : array();
    // SSL配置ID， 关联cdn_sslconf表ID
    $sslconf_id = isset($data['sslconf_id']) ? (int) $data['sslconf_id'] : 0;
    // 源站协议
    $origin_protocol = isset($data['origin_protocol']) ? (int) $data['origin_protocol'] : 0;
    // 是否启用HTTPS跳转 0不启用 1启用，该项只有SSL加速时才生效
    $ssl_redirect = isset($data['ssl_redirect']) ? (int) $data['ssl_redirect'] : 0;
    // 监控地址
    $monitorurl = isset($data['monitorurl']) ? check_plain(trim($data['monitorurl'])) : '';
    //备注
    $note = isset($data['note']) ? check_plain(trim($data['note'])) : '';
    // RTMP加速、动态加速重置缓存策略
    if(in_array($type, array('rtmp', 'dynamic'))) {
      $cacheconf = array();
    }

    // 高级设置之防盗链
    $accesskey = isset($data['accesskey']) ? check_plain($data['accesskey']) : '';
    $accesskey_hashmethod = isset($data['accesskey_hashmethod']) ? check_plain($data['accesskey_hashmethod']) : '';
    $accesskey_signature = isset($data['accesskey_signature']) ? check_plain(trim($data['accesskey_signature'])) : '';
    $accesskey_exclude = isset($data['accesskey_exclude']) ? check_plain(trim($data['accesskey_exclude'])) : '';

    // 高级设置之referer
    // referer设置
    $referer_enable = isset($data['referer_enable']) ? check_plain($data['referer_enable']) : '';
    $referer_white = isset($data['referer_white']) ? trim(urldecode($data['referer_white'])) : '';
    $referer_black = isset($data['referer_black']) ? trim(urldecode($data['referer_black'])) : '';
    $allow_null_referer = isset($data['allow_null_referer']) ? check_plain($data['allow_null_referer']) : '';
    $access_referer_suffix = isset($data['access_referer_suffix']) ? check_plain(trim($data['access_referer_suffix'])) : '';
    $access_deny_url = isset($data['access_deny_url']) ? check_plain(trim($data['access_deny_url'])) : '';

    // user agent设置
    $ua_enable = isset($data['ua_enable']) ? check_plain($data['ua_enable']) : '';
    $ua_white = isset($data['ua_white']) ? trim(urldecode($data['ua_white'])) : '';
    $ua_black = isset($data['ua_black']) ? trim(urldecode($data['ua_black'])) : '';
    $allow_null_ua = isset($data['allow_null_ua']) ? check_plain($data['allow_null_ua']) : '';
    $access_ua_suffix = isset($data['access_ua_suffix']) ? check_plain($data['access_ua_suffix']) : '';

    // 高级设置之其他
    $cache_ignore_query = isset($data['cache_ignore_query']) ? check_plain($data['cache_ignore_query']) : '';

    // 高级设置之header
    $add_server_header = isset($data['add_server_header']) ? check_plain($data['add_server_header']) : '';

    $url = Request::$api_url . '/API/cdn/domainAdd';

    $send_data = array(
      'token' => $token,
      'name' => $name,
      'type' => $type,
      'ip' => $ip,
      'icp' => $icp,
      'siteinfo' => $siteinfo,
      'authorize' => $authorize,
      'cachetype' => $cachetype,
      'cacheconf' => $cacheconf,
      'sslconf_id' => $sslconf_id,
      'origin_protocol' => $origin_protocol,
      'ssl_redirect' => $ssl_redirect,
      'monitorurl' => $monitorurl,
      'originback' => $originback,
      'note' => $note,
      'accesskey' => $accesskey,
      'accesskey_hashmethod' => $accesskey_hashmethod,
      'accesskey_signature' => $accesskey_signature,
      'accesskey_exclude' => $accesskey_exclude,
      'referer_enable' => $referer_enable,
      'allow_null_referer' => $allow_null_referer,
      'access_referer_suffix' => $access_referer_suffix,
      'access_deny_url' => $access_deny_url,
      'ua_enable' => $ua_enable,
      'allow_null_ua' => $allow_null_ua,
      'access_ua_suffix' => $access_ua_suffix,
      'cache_ignore_query' => $cache_ignore_query,
      'add_server_header' => $add_server_header
    );

    if(isset($data['referer_white'])) {
      $send_data['referer_white'] = $referer_white;
    }
    else if(isset($data['referer_black'])) {
      $send_data['referer_black'] = $referer_black;
    }

    if(isset($data['ua_white'])) {
      $send_data['ua_white'] = $ua_white;
    }
    else if(isset($data['ua_black'])) {
      $send_data['ua_black'] = $ua_black;
    }

    $return = Request::sendRequest($url, $send_data);
    return $return;
  }

  /**
   * 更新频道
   * @param $data
   * @return array
   */
  public function domainUpdate($data) {
    $token = (new Token())->token();
    if(!$token) {
      return array(
        'code' => 0,
        'message' => 'unable to get token'
      );
    }
    $id = isset($data['id']) && !empty($data['id']) ? (int) $data['id'] : 0;
    if(!$id) {
      return array(
        'code' => 0,
        'message' => 'id is required',
      );
    }

    $url = Request::$api_url . '/API/cdn/domainUpdate';

    $send_data = array(
      'token' => $token,
      'id' => $id
    );

    //星标
    $mark = isset($data['mark']) ? (int) check_plain(trim($data['mark'])) : 99;
    if($mark === 0 || $mark === 1) {
      $send_data['mark'] = $mark;
    }

    //ICP备案号
    $icp = isset($data['icp']) ? check_plain(trim($data['icp'])) : '';
    if(!empty($icp)) {
      $send_data['icp'] = $icp;
    }

    //源站
    $ip = isset($data['ip']) ? $data['ip'] : array();
    if(!empty($ip)) {
      $send_data['ip'] = $ip;
    }

    // 缓存类型
    if(isset($data['cachetype'])) {
      $cachetype = check_plain(trim($data['cachetype']));
      $send_data['cachetype'] = $cachetype;
    }
    /**
     * cacheconf数据格式
     * $cacheconf = array(
     * 'type' => 1缓存 2不缓存 3缓存首页,
     * 'pattern' => $pattern, // 缓存后缀
     * 'ttl' => 5, // 缓存时间
     * 'unit' => 'd', // 缓存时间类型 m分钟 h小时 d天
     * 'path' => '/images/',
     * )
     */
    if(isset($data['cacheconf'])) {
      $cacheconf = $data['cacheconf'];
      $send_data['cacheconf'] = $cacheconf;
    }

    if(isset($data['authorize'])) {
      // 是否授权 0未授权 1已授权
      $authorize = (int) $data['authorize'];
      $send_data['authorize'] = $authorize;
    }

    //网站类型
    if(isset($data['siteinfo'])) {
      $siteinfo = check_plain(trim($data['siteinfo']));
      $send_data['siteinfo'] = $siteinfo;
    }

    //ssl证书ID
    if(isset($data['sslconf_id'])) {
      $sslconf_id = (int) $data['sslconf_id'];
      $send_data['sslconf_id'] = $sslconf_id;
    }

    // 是否启用HTTPS跳转 0不启用 1启用，该项只有SSL加速时才生效
    if(isset($data['ssl_redirect'])) {
      $ssl_redirect = (int) $data['ssl_redirect'];
      $send_data['ssl_redirect'] = $ssl_redirect;
    }

    // 源站协议 取值[0(HTTPS),1(HTTP)]，默认0
    if(isset($data['origin_protocol'])) {
      $origin_protocol = (int) $data['origin_protocol'];
      $send_data['origin_protocol'] = $origin_protocol;
    }

    //备份源站
    if(isset($data['originback'])) {
      $originback = $data['originback'];
      $send_data['originback'] = $originback;
    }

    // 监控地址
    if(isset($data['monitorurl'])) {
      $monitorurl = check_plain(trim($data['monitorurl']));
      $send_data['monitorurl'] = $monitorurl;
    }

    // 备注
    if(isset($data['note'])) {
      $note = check_plain(trim($data['note']));
      $send_data['note'] = $note;
    }

    // 高级设置之防盗链
    if(isset($data['accesskey'])) {
      // 防盗链通用设置
      $accesskey = check_plain(trim($data['accesskey']));
      $send_data['accesskey'] = $accesskey;
      if($accesskey == 'on') {
        $accesskey_hashmethod = isset($data['accesskey_hashmethod']) ? check_plain($data['accesskey_hashmethod']) : '';
        $send_data['accesskey_hashmethod'] = $accesskey_hashmethod;
        $accesskey_signature = isset($data['accesskey_signature']) ? check_plain(trim($data['accesskey_signature'])) : '';
        $send_data['accesskey_signature'] = $accesskey_signature;
        $accesskey_exclude = isset($data['accesskey_exclude']) ? check_plain(trim($data['accesskey_exclude'])) : '';
        $send_data['accesskey_exclude'] = $accesskey_exclude;
      }
    }

    // referer设置
    if(isset($data['referer_enable'])) {
      $referer_enable = check_plain($data['referer_enable']);
      $send_data['referer_enable'] = $referer_enable;
      if($referer_enable == 'on') {
        if(isset($data['referer_white'])) {
          $referer_white = isset($data['referer_white']) ? trim(urldecode($data['referer_white'])) : '';
          $send_data['referer_white'] = $referer_white;
        }
        else if(isset($data['referer_black'])) {
          $referer_black = isset($data['referer_black']) ? trim(urldecode($data['referer_black'])) : '';
          $send_data['referer_black'] = $referer_black;
        }

        $allow_null_referer = isset($data['allow_null_referer']) ? check_plain($data['allow_null_referer']) : '';
        if(!empty($allow_null_referer)) {
          $send_data['allow_null_referer'] = $allow_null_referer;
        }
        $access_referer_suffix = isset($data['access_referer_suffix']) ? check_plain(trim($data['access_referer_suffix'])) : '';
        $send_data['access_referer_suffix'] = $access_referer_suffix;
        $access_deny_url = isset($data['access_deny_url']) ? check_plain(trim($data['access_deny_url'])) : '';
        $send_data['access_deny_url'] = $access_deny_url;
      }
      else {
        $send_data['allow_null_referer'] = 'off';
        $send_data['access_referer_suffix'] = '';
        $send_data['access_deny_url'] = '';
      }
    }

    // user agent设置
    if(isset($data['ua_enable'])) {
      $ua_enable = check_plain($data['ua_enable']);
      $send_data['ua_enable'] = $ua_enable;
      if($ua_enable == 'on') {
        if(isset($data['ua_white'])) {
          $ua_white = isset($data['ua_white']) ? trim(urldecode($data['ua_white'])) : '';
          $send_data['ua_white'] = $ua_white;
        }
        else if (isset($data['ua_black'])) {
          $ua_black = isset($data['ua_black']) ? trim(urldecode($data['ua_black'])) : '';
          $send_data['ua_black'] = $ua_black;
        }

        $allow_null_ua = isset($data['allow_null_ua']) ? check_plain($data['allow_null_ua']) : '';
        if(!empty($allow_null_ua)) {
          $send_data['allow_null_ua'] = $allow_null_ua;
        }
        $access_ua_suffix = isset($data['access_ua_suffix']) ? check_plain($data['access_ua_suffix']) : '';
        $send_data['access_ua_suffix'] = $access_ua_suffix;
      }
      else {
        $send_data['allow_null_ua'] = 'off';
        $send_data['access_ua_suffix'] = '';
      }
    }

    // 高级设置之其他
    if(isset($data['cache_ignore_query'])) {
      $cache_ignore_query = check_plain($data['cache_ignore_query']);
      $send_data['cache_ignore_query'] = $cache_ignore_query;
    }

    // 高级设置之回源Host
    if(isset($data['add_server_header'])) {
      $add_server_header = check_plain($data['add_server_header']);
      $send_data['add_server_header'] = $add_server_header;
    }

    $return = Request::sendRequest($url, $send_data);
    return $return;
  }

  /**
   * 开启频道
   * @param $data
   * @return array
   */
  public function domainStart($data) {
    $token = (new Token())->token();
    if(!$token) {
      return array(
        'code' => 0,
        'message' => 'unable to get token'
      );
    }
    /**
     * 数组格式，批量操作
     * $data = array(1,2,3)
     */
    $data = isset($data['data']) ? $data['data'] : array();
    if(empty($data)) {
      return array(
        'code' => 0,
        'message' => 'id is required',
      );
    }

    $url = Request::$api_url . '/API/cdn/domainStart';

    $send_data = array(
      'token' => $token,
      'domain_ids' => $data
    );

    $return = Request::sendRequest($url, $send_data);
    return $return;
  }

  /**
   * 关闭频道
   * @param $data
   * @return array
   */
  public function domainClose($data) {
    $token = (new Token())->token();
    if(!$token) {
      return array(
        'code' => 0,
        'message' => 'unable to get token'
      );
    }
    /**
     * 数组格式，批量操作
     * $data = array(1,2,3)
     */
    $data = isset($data['data']) ? $data['data'] : array();
    if(empty($data)) {
      return array(
        'code' => 0,
        'message' => 'id is required',
      );
    }

    $url = Request::$api_url . '/API/cdn/domainClose';

    $send_data = array(
      'token' => $token,
      'domain_ids' => $data
    );

    $return = Request::sendRequest($url, $send_data);
    return $return;
  }

  /**
   * 删除频道
   * @param $data
   * @return array
   */
  public function domainDelete($data) {
    $token = (new Token())->token();
    if(!$token) {
      return array(
        'code' => 0,
        'message' => 'unable to get token'
      );
    }
    /**
     * 数组格式，批量操作
     * $data = array(1,2,3)
     */
    $data = isset($data['data']) ? $data['data'] : array();
    if(empty($data)) {
      return array(
        'code' => 0,
        'message' => 'id is required',
      );
    }

    $url = Request::$api_url . '/API/cdn/domainDelete';

    $send_data = array(
      'token' => $token,
      'domain_ids' => $data
    );

    $return = Request::sendRequest($url, $send_data);
    return $return;
  }

  /**
   * 频道日志列表
   * @param $data
   * @return array
   */
  public function domainLogList($data) {
    $token = (new Token())->token();
    if(!$token) {
      return array(
        'code' => 0,
        'message' => 'unable to get token'
      );
    }

    // 频道主键id
    $id = isset($data['id']) && !empty($data['id']) ? (int) $data['id'] : 0;
    if(!$id) {
      return array(
        'code' => 0,
        'message' => 'id is required',
      );
    }
    //目录
    $dir = isset($data['dir']) ? check_plain(trim($data['dir'])) : '';
    //目录，一般用于泛域名
    $dir1 = isset($data['dir1']) ? check_plain(trim($data['dir1'])) : '';
    // 日志起始与终止的日期：如2014-12-04，默认2个月前到现在为止
    $begintime = isset($data['begintime']) && !empty($data['begintime']) ? check_plain(trim($data['begintime'])) : date('Y-m-d', strtotime('-2 months'));
    $endtime = isset($data['endtime']) && !empty($data['endtime']) ? check_plain(trim($data['endtime'])) : date('Y-m-d', time());
    // 分页相关数据
    $page = isset($data['page']) && !empty($data['page']) ? (int) $data['page'] : 1;
    $limit = isset($data['limit']) && !empty($data['limit']) ? (int) $data['limit'] : 30;

    $url = Request::$api_url . '/API/cdn/domainLogList';

    $send_data = array(
      'token' => $token,
      'id' => $id,
      'dir' => $dir,
      'dir1' => $dir1,
      'begintime' => $begintime,
      'endtime' => $endtime,
      'page' => $page,
      'limit' => $limit
    );

    $return = Request::sendRequest($url, $send_data);
    return $return;
  }

  /**
   * 频道带宽信息
   * @param $data
   * @return array
   */
  public function domainBandwidth($data) {
    $token = (new Token())->token();
    if(!$token) {
      return array(
        'code' => 0,
        'message' => 'unable to get token'
      );
    }
    //加速频道ID
    $domain_ids = isset($data['domain_ids']) && !empty($data['domain_ids']) ? check_plain($data['domain_ids']) : '';
    //加速类型 取值[cloud(云分发),static(静态),dynamic(动态),rtmp(RTMP加速),music(音视加速),hls(HLS加速),download(下载加速)]
    $serviceTypes = isset($data['serviceTypes']) && !empty($data['serviceTypes']) ? check_plain($data['serviceTypes']) : '';
    //开始日期，Y-m-d
    $begintime = isset($data['begintime']) ? check_plain($data['begintime']) : '';
    //结束日期，Y-m-d
    $endtime = isset($data['endtime']) ? check_plain($data['endtime']) : '';
    //查看类型，取值范围[today(当天),yesterday(昨天),week(本周),month(当月),last_month(上月),custom(自定义)]
    $viewtype = isset($data['viewtype']) ? check_plain(trim($data['viewtype'])) : '';

    $url = Request::$api_url . '/API/cdn/domainBandwidth';

    $send_data = array(
      'token' => $token,
      'domain_ids' => $domain_ids,
      'begintime' => $begintime,
      'endtime' => $endtime,
      'viewtype' => $viewtype,
      'serviceTypes' => $serviceTypes
    );

    $return = Request::sendRequest($url, $send_data);
    return $return;
  }

  /**
   * 频道统计分析
   * @param $data
   * @return  array
   */
  public function domainLogstat($data) {
    $token = (new Token())->token();
    if(!$token) {
      return array(
        'code' => 0,
        'message' => 'unable to get token'
      );
    }
    //获取数据的类型，取值[cache, code, country, province, isp]
    $types = isset($data['types']) && !empty($data['types']) ? check_plain(trim($data['types'])) : '';
    //频道ID
    $domain_ids = isset($data['domain_ids']) && !empty($data['domain_ids']) ? check_plain($data['domain_ids']) : '';
    //加速类型 取值[cloud(云分发),static(静态),dynamic(动态),rtmp(RTMP加速),music(音视加速),hls(HLS加速),download(下载加速)]
    $serviceTypes = isset($data['serviceTypes']) && !empty($data['serviceTypes']) ? check_plain($data['serviceTypes']) : '';
    //开始日期，Y-m-d
    $begintime = isset($data['begintime']) ? check_plain($data['begintime']) :  '';
    //结束日期 ，Y-m-d
    $endtime = isset($data['endtime']) ? check_plain($data['endtime']) : '';
    //类型，取值范围[today(当天),yesterday(昨天),week(本周),month(当月),last_month(上月),custom(自定义)]
    $viewtype = isset($data['viewtype']) ? check_plain(trim($data['viewtype'])) : '';

    $url = Request::$api_url . '/API/cdn/domainLogstat';

    $send_data = array(
      'token' => $token,
      'types' => $types,
      'domain_ids' => $domain_ids,
      'begintime' => $begintime,
      'endtime' => $endtime,
      'viewtype' => $viewtype,
      'serviceTypes' => $serviceTypes
    );

    $return = Request::sendRequest($url, $send_data);
    return $return;
  }

  /**
   * SSL证书列表
   * @param $data
   * @return array
   */
  public function sslList($data) {
    $token = (new Token())->token();
    if(!$token) {
      return array(
        'code' => 0,
        'message' => 'unable to get token'
      );
    }

    // 分页相关数据
    $page = isset($data['page']) && !empty($data['page']) ? (int) $data['page'] : 1;
    $limit = isset($data['limit']) && !empty($data['limit']) ? (int) $data['limit'] : 30;

    $url = Request::$api_url . '/API/cdn/sslList';

    $send_data = array(
      'token' => $token,
      'page' => $page,
      'limit' => $limit
    );

    //按ID查询
    $id = isset($data['id']) ? (int) $data['id'] : 0;
    if($id) {
      $send_data['id'] = $id;
    }

    $return = Request::sendRequest($url, $send_data);
    return $return;
  }

  /**
   * SSL证书新增
   * @param $data
   * @return array
   */
  public function sslAdd($data) {
    $token = (new Token())->token();
    if(!$token) {
      return array(
        'code' => 0,
        'message' => 'unable to get token'
      );
    }

    // 证书名称
    $name = isset($data['name']) && !empty($data['name']) ? check_plain(trim($data['name'])) : '';
    // 证书文件
    $ssl_crt = isset($data['ssl_crt']) && !empty($data['ssl_crt']) ? check_plain($data['ssl_crt']) : '';
    // 证书密钥
    $ssl_key = isset($data['ssl_key']) && !empty($data['ssl_key']) ? check_plain($data['ssl_key']) : '';
    // 中级证书
    $ssl_ca = isset($data['ssl_ca']) && !empty($data['ssl_ca']) ? check_plain($data['ssl_ca']) : '';

    if(empty($ssl_crt) || empty($ssl_key)) {
      return array(
        'code' => 0,
        'message' => 'please upload your cert',
      );
    }

    $url = Request::$api_url . '/API/cdn/sslAdd';

    $send_data = array(
      'token' => $token,
      'name' => $name,
      'ssl_crt' => $ssl_crt,
      'ssl_key' => $ssl_key,
      'ssl_ca' => $ssl_ca
    );

    $return = Request::sendRequest($url, $send_data);
    return $return;
  }

  /**
   * SSL证书修改
   * @param $data
   * @return array
   */
  public function sslUpdate($data) {
    $token = (new Token())->token();
    if(!$token) {
      return array(
        'code' => 0,
        'message' => 'unable to get token'
      );
    }

    $id = isset($data['id']) && !empty($data['id']) ? (int) $data['id'] : 0;
    if(!$id) {
      return array(
        'code' => 0,
        'message' => 'id is required',
      );
    }

    $url = Request::$api_url . '/API/cdn/sslUpdate';

    $send_data = array(
      'token' => $token,
      'id' => $id
    );

    if(isset($data['name'])) {
      $name = check_plain(trim($data['name']));
      $send_data['name'] = $name;
    }

    if(isset($data['ssl_crt'])) {
      $ssl_crt = check_plain($data['ssl_crt']);
      $send_data['ssl_crt'] = $ssl_crt;
    }

    if(isset($data['ssl_key'])) {
      $ssl_key = check_plain($data['ssl_key']);
      $send_data['ssl_key'] = $ssl_key;
    }

    if(isset($data['ssl_ca'])) {
      $ssl_ca = check_plain($data['ssl_ca']);
      $send_data['ssl_ca'] = $ssl_ca;
    }

    $return = Request::sendRequest($url, $send_data);
    return $return;
  }

  /**
   * SSL证书删除
   * @param $data
   * @return array
   */
  public function sslDelete($data) {
    $token = (new Token())->token();
    if(!$token) {
      return array(
        'code' => 0,
        'message' => 'unable to get token'
      );
    }

    $id = isset($data['id']) && !empty($data['id']) ? (int) $data['id'] : 0;
    if(!$id) {
      return array(
        'code' => 0,
        'message' => 'id is required'
      );
    }

    $url = Request::$api_url . '/API/cdn/sslDelete';

    $send_data = array(
      'token' => $token,
      'id' => $id
    );

    $return = Request::sendRequest($url, $send_data);
    return $return;
  }

  /**
   * 频道实时请求书、带宽
   * @param $data
   * @return array
   */
  public function domainCurrentData($data) {
    $token = (new Token())->token();
    if(!$token) {
      return array(
        'code' => 0,
        'message' => 'unable to get token'
      );
    }

    $ids = isset($data['ids']) ? check_plain($data['ids']) : '';
    if(empty($ids)) {
      return array(
        'code' => 0,
        'message' => 'id is required',
      );
    }

    $url = Request::$api_url . '/API/cdn/domainCurrentData';

    $send_data = array(
      'token' => $token,
      'ids' => $ids
    );

    $return = Request::sendRequest($url, $send_data);
    return $return;
  }

  /**
   * 刷新列表
   * @param $data
   * @return array
   */
  public function refreshList($data) {
    $token = (new Token())->token();
    if(!$token) {
      return array(
        'code' => 0,
        'message' => 'unable to get token'
      );
    }

    //是否预取 1 是 0 否
    $preload = isset($data['preload']) ? (int) $data['preload'] : 0;
    // 分页相关数据
    $page = isset($data['page']) && !empty($data['page']) ? (int) $data['page'] : 1;
    $limit = isset($data['limit']) && !empty($data['limit']) ? (int) $data['limit'] : PAGER_LIMIT;

    $url = Request::$api_url . '/API/cdn/refreshList';

    $send_data = array(
      'token' => $token,
      'preload' => $preload,
      'page' => $page,
      'limit' => $limit
    );

    // 文件刷新file 目录刷新dir
    $type = isset($data['type']) && !empty($data['type']) ? check_plain(trim($data['type'])) : '';
    if(!empty($type)) {
      $send_data['type'] = $type;
    }

    $return = Request::sendRequest($url, $send_data);
    return $return;
  }

  /**
   * 提交刷新
   * @param $data
   * @return array
   */
  public function refresh($data) {
    $token = (new Token())->token();
    if(!$token) {
      return array(
        'code' => 0,
        'message' => 'unable to get token'
      );
    }
    //刷新类型 file 文件 dir 目录
    $type = isset($data['type']) && !empty($data['type']) ? check_plain(trim($data['type'])) : '';
    //刷新url
    $urls = isset($data['urls']) && !empty($data['urls']) ? trim(urldecode($data['urls'])) : '';
    //url分隔符，多个url使用该符号分隔，默认,
    $partition = isset($data['partition']) && !empty($data['partition']) ? check_plain(trim($data['partition'])) : ',';
    if(empty($type) || empty($urls)) {
      return array(
        'code' => 0,
        'message' => 'type and urls are required',
      );
    }

    $url = Request::$api_url . '/API/cdn/refresh';

    $send_data = array(
      'token' => $token,
      'type' => $type,
      'urls' => $urls,
      'partition' => $partition
    );

    $return = Request::sendRequest($url, $send_data);
    return $return;
  }

  /**
   * 按ID刷新
   * @param $data
   * @return array
   */
  public function refreshById($data) {
    $token = (new Token())->token();
    if(!$token) {
      return array(
        'code' => 0,
        'message' => 'unable to get token'
      );
    }

    $id = isset($data['id']) && !empty($data['id']) ? (int) $data['id'] : 0;
    if(!$id) {
      return array(
        'code' => 0,
        'message' => 'id is required',
      );
    }

    $url = Request::$api_url . '/API/cdn/refreshById';

    $send_data = array(
      'token' => $token,
      'id' => $id
    );

    $return = Request::sendRequest($url, $send_data);
    return $return;
  }

  /**
   * 提交预取
   * @param $data
   * @return array
   */
  public function preload($data) {
    $token = (new Token())->token();
    if(!$token) {
      return array(
        'code' => 0,
        'message' => 'unable to get token'
      );
    }

    //预取url
    $urls = isset($data['urls']) && !empty($data['urls']) ? trim(urldecode($data['urls'])) : '';
    //url分隔符，多个url使用该符号分隔，默认,
    $partition = isset($data['partition']) && !empty($data['partition']) ? check_plain(trim($data['partition'])) : ',';
    //预取类型 1 全网预取 0 非全网
    $preloadtype = isset($data['preloadtype']) ? (int) $data['preloadtype'] : 0;

    if(empty($type) || empty($urls)) {
      return array(
        'code' => 0,
        'message' => 'type and urls are required',
      );
    }

    $url = Request::$api_url . '/API/cdn/preload';

    $send_data = array(
      'token' => $token,
      'urls' => $urls,
      'partition' => $partition,
      'preloadtype' => $preloadtype
    );

    $return = Request::sendRequest($url, $send_data);
    return $return;
  }

  /**
   * 按ID预取
   * @param $data
   */
  public function preloadById($data) {
    $token = (new Token())->token();
    if(!$token) {
      return array(
        'code' => 0,
        'message' => 'unable to get token'
      );
    }

    $id = isset($data['id']) && !empty($data['id']) ? (int) $data['id'] : 0;
    if(!$id) {
      return array(
        'code' => 0,
        'message' => 'id is required',
      );
    }

    $url = Request::$api_url . '/API/cdn/preloadById';

    $send_data = array(
      'token' => $token,
      'id' => $id
    );

    $return = Request::sendRequest($url, $send_data);
    return $return;
  }

  /**
   * 删除刷新、预取
   * @param $data
   * @return array
   */
  public function refreshDelete($data) {
    $token = (new Token())->token();
    if(!$token) {
      return array(
        'code' => 0,
        'message' => 'unable to get token'
      );
    }

    $ids = isset($data['ids']) && !empty($data['ids']) ? check_plain(trim($data['ids'])) : 0;
    if(!$ids || empty($ids)) {
      return array(
        'code' => 0,
        'message' => 'id is required',
      );
    }

    $url = Request::$api_url . '/API/cdn/refreshDelete';

    $send_data = array(
      'token' => $token,
      'ids' => $ids,
    );

    $return = Request::sendRequest($url, $send_data);
    return $return;
  }

  /**
   * 获取CDN账单（按用户）
   * @param $data
   * @return array
   */
  public function getBillByUser($data) {
    $token = (new Token())->token();
    if(!$token) {
      return array(
        'code' => 0,
        'message' => 'unable to get token'
      );
    }

    //加速类型，取值范围[cloud(云分发),download(下载),hls(HLS),music(音视),rtmp(RTMP),static(静态)]
    $type = isset($data['type']) && !empty($data['type']) ? array_filter($data['type']) : array();
    //月份 如201506表示2015年6月份，最多查询最近12个月的数据
    $months = isset($data['months']) && !empty($data['months']) ? check_plain($data['months']) : '';
    //子账号ID，查询子账号账单时必选
    $sub_uid = isset($data['sub_uid']) ? (int) $data['sub_uid'] : 0;

    $url = Request::$api_url . '/API/cdn/getBillByUser';

    $send_data = array(
      'token' => $token,
      'type' => $type,
      'months' => $months,
      'sub_uid' => $sub_uid
    );

    $return = Request::sendRequest($url, $send_data);
    return $return;
  }

  /**
   * 获取CDN账单（按频道）
   * @param $data
   * @return array
   */
  public function getBillByDomainID($data) {
    $token = (new Token())->token();
    if(!$token) {
      return array(
        'code' => 0,
        'message' => 'unable to get token'
      );
    }

    //频道ID,数组 例如 ： array(1,2,3)
    $domain_ids = isset($data['domain_ids']) && !empty($data['domain_ids']) ? array_filter($data['domain_ids']) : array();
    //开始时间，取值[unixtime]
    $begintime = isset($data['begintime']) && !empty($data['begintime']) ? (int) $data['begintime'] : 0;
    //结束时间，取值[unixtime]
    $endtime = isset($data['endtime']) && !empty($data['endtime']) ? (int) $data['endtime'] : 0;

    $url = Request::$api_url . '/API/cdn/getBillByDomainID';

    $send_data = array(
      'token' => $token,
      'domain_ids' => $domain_ids,
      'begintime' => $begintime,
      'endtime' => $endtime
    );

    $return = Request::sendRequest($url, $send_data);
    return $return;
  }

  /**
   * 获取CDN节点
   * @param $data
   * @return array
   */
  public function nodes($data) {
    $token = (new Token())->token();
    if(!$token) {
      return array(
        'code' => 0,
        'message' => 'unable to get token'
      );
    }

    //类型 core 核心 edge 边缘
    $type = isset($data['type']) && !empty($data['type']) ? check_plain($data['type']) :'';
    //频道名称
    $name = isset($data['name']) && !empty($data['name']) ? check_plain($data['name']) :'';

    if(empty($type) || empty($name)) {
      return array(
        'code' => 0,
        'message' => 'type and name are required'
      );
    }

    $url = Request::$api_url . '/API/cdn/nodes';

    $send_data = array(
      'token' => $token,
      'type' => $type,
      'name' => $name,
    );

    $return = Request::sendRequest($url, $send_data);
    return $return;
  }

  /**
   * 根据客户端IP返回最优的CDN节点
   * @param $data
   * @return array
   */
  public function getNodesByClientIP($data) {
    $token = (new Token())->token();
    if(!$token) {
      return array(
        'code' => 0,
        'message' => 'unable to get token'
      );
    }

    //客户端IP
    $client_ip = isset($data['client_ip']) && !empty($data['client_ip']) ? check_plain($data['client_ip']) :'';
    //频道名称
    $name = isset($data['name']) && !empty($data['name']) ? check_plain($data['name']) :'';

    if(empty($client_ip) || empty($name)) {
      return array(
        'code' => 0,
        'message' => 'client_ip and name are required'
      );
    }

    $url = Request::$api_url . '/API/cdn/getNodesByClientIP';

    $send_data = array(
      'token' => $token,
      'client_ip' => $client_ip,
      'name' => $name,
    );

    $return = Request::sendRequest($url, $send_data);
    return $return;
  }
}