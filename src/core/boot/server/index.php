<?php

require_once("contracts/res.php");

/**
 * ArnelifyServer (Experimental)
 * 
 * IMPORTANT:
 * This facade is still in development 
 * and temporarily simulates a real ArnelifyServer written in C and C++.
 * GitHub: https://github.com/arnelify/arnelify-server-cpp
 * 
 */
class ArnelifyServerExperimental {

  protected $opts = [];
  protected $lib = null;

  /**
   * getKeys
   *
   * @param  string $name
   * @return array
   */
  public function getKeys($name): array {
    $patternStart = strpos($name, "[]");
    $hasPattern = $patternStart !== false;
    if (!$hasPattern) {
      return [$name];
    }

    $keys = [];
    $buffer = substr($name, 0, $patternStart);

    $keyStart = strpos($buffer, "[");
    $hasKeyStart = $keyStart !== false;
    if (!$hasKeyStart) {
      return [$buffer];
    }

    $keys[] = substr($buffer, 0, $keyStart);
    $buffer = substr($buffer, $keyStart);
    $keyStart = strpos($buffer, "[");

    while ($keyStart !== false) {
      $keyEnd = strpos($buffer, "]", $keyStart + 1);
      $hasKeyEnd = $keyEnd !== false;
      if (!$hasKeyEnd) {
        return [$name];
      }

      $key = substr($buffer, $keyStart + 1, $keyEnd - $keyStart - 1);
      $innerStart = strpos($key, "[");
      $hasInner = $innerStart !== false;
      if ($hasInner) {
        return [$name];
      }

      $keys[] = $key;
      $keyStart = strpos($buffer, "[", $keyEnd + 1);
    }

    return $keys;
  }

  /**
   * getCtx
   *
   * @return array
   */
  public function getRequest(): array {
    $req = [
      "_state" => [
        "client" => null,
        "cookie" => $_COOKIE,
        "headers" => getallheaders(),
        "method" => $_SERVER['REQUEST_METHOD'] ?? 'GET',
        "path" => $_SERVER['REQUEST_URI'],
        "version" => 'HTTP/1.1',
      ],

      "body" => $_POST,
      "files" => $_FILES,
      "query" => $_GET,
    ];

    if (!isset($req["_state"]["client"]) && isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
      $req["_state"]["client"] = $_SERVER["HTTP_X_FORWARDED_FOR"];
    }

    if (!isset($req["_state"]["client"]) && isset($_SERVER["HTTP_CLIENT_IP"])) {
      $req["_state"]["client"] = $_SERVER["HTTP_CLIENT_IP"];
    }

    if (!isset($req["_state"]["client"]) && isset($_SERVER["REMOTE_ADDR"])) {
      $req["_state"]["client"] = $_SERVER["REMOTE_ADDR"];
    }

    $hasQuery = strpos($_SERVER['REQUEST_URI'], '?');
    if ($hasQuery) $req["_state"]["path"] = strtok($_SERVER['REQUEST_URI'], '?');
    return $req;
  }

  public function __construct($opts) {
    // $arch = php_uname('m');
    // $libPath = __DIR__ . "/bin/arnelify_server_amd64.so";
    // if (strpos($arch, 'aarch64') !== false) {
    //   $libPath = __DIR__ . "/bin/arnelify_server_arm64.so";
    // } else {
    //   echo 'CPU platform isn\'t supported.';
    //   exit(1);
    // }

    // $this->lib = FFI::cdef("
    //   void server_create(const char *cOpts);
    //   void server_destroy();
    //   void server_set_handler(const char *(*cHandler)(const char *), const int hasRemove);
    //   void server_start(void (*cCallback)(const char *, const int));
    //   void server_stop();",
    //   $libPath
    // );

    // $this->lib->server_create(json_encode($this->opts));
    $this->opts = $opts;
  }

  /**
   * start
   *
   * @param  callable $callback
   * @return void
   */
  public function setHandler($handler): void {
    //$this->server_set_handler($this->handler);

    $req = $this->getRequest();
    $res = new Res();

    $handler($req, $res);
  }

  public function start(): void {
    //$this->server_start($callback);
    exit(0);
  }

  public function stop(): void {
    //$this->server_stop();
    exit(0);
  }
}