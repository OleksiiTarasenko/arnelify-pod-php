<?php

/**
 * Res
 */
class Res {

  private $opts = [];

  private $res = [
    "code" => 200,
    "body" => "",
    "filePath" => "",
    "headers" => [
      "Content-Type" => "application/json"
    ],
    "isStatic" => false
  ];

  /**
   * getMime
   *
   * @param  string $extension
   * @return string
   */
  private function getMime(&$extension): string {
    $charset = 'UTF-8'; //get it from $this->opts->TRANSMITTER_CHARSET;

    $mime = [
      ".avi" => "video/x-msvideo",
      ".css" => "text/css; charset=" . $charset,
      ".csv" => "text/csv; charset=" . $charset,
      ".eot" => "font/eot",
      ".gif" => "image/gif",
      ".htm" => "text/html; charset=" . $charset,
      ".html" => "text/html; charset=" . $charset,
      ".ico" => "image/x-icon",
      ".jpeg" => "image/jpeg",
      ".jpg" => "image/jpeg",
      ".js" => "application/javascript; charset=" . $charset,
      ".json" => "application/json; charset=" . $charset,
      ".mkv" => "video/x-matroska",
      ".mov" => "video/quicktime",
      ".mp3" => "audio/mpeg",
      ".mp4" => "video/mp4",
      ".otf" => "font/otf",
      ".png" => "image/png",
      ".svg" => "image/svg+xml; charset=" . $charset,
      ".ttf" => "font/ttf",
      ".txt" => "text/plain; charset=" . $charset,
      ".wasm" => "application/wasm",
      ".wav" => "audio/wav",
      ".weba" => "audio/webm",
      ".webp" => "image/webp",
      ".woff" => "font/woff",
      ".woff2" => "font/woff2",
      ".xml" => "application/xml; charset=" . $charset,
    ];

    return $mime[$extension];
  }

  public function __construct() { //$opts
    //$this->opts = $opts; //get TRANSMITTER_OPTS
  }

  /**
   * addBody
   *
   * @param  string $chunk
   * @return void
   */
  public function addBody($chunk): void {
    if (!is_string($chunk)) {
      echo 'Error: $chunk must be a string.';
      exit(1);
    }

    if (mb_strlen($this->res["filePath"])) {
      echo 'Error: Can\'t add body to a Response that contains a file.';
      exit(1);
    }

    $this->res["body"] = $this->res["body"] . $chunk;
  }

  /**
   * End
   *
   * @return void
   */
  public function end(): void {
    http_response_code($this->res["code"]);

    foreach ($this->res["headers"] as $key => $value) {
      header($key . ': ' . $value);
    }

    $hasFile = mb_strlen($this->res["filePath"]);
    if ($hasFile) {
      if (!$this->res["isStatic"]) {
        $filename = basename($this->res["filePath"]);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
      }

      $isRelativePath = mb_substr($this->res["filePath"], 0, 1) === ".";
      if ($isRelativePath) {
        $hasSrcDir = mb_strlen($_SERVER['DOCUMENT_ROOT'])
          && strpos($_SERVER['DOCUMENT_ROOT'], '/src');
        if (!$hasSrcDir) {
          echo 'Error: invalid $_SERVER[\'DOCUMENT_ROOT\'].';
          exit(1);
        }

        $srcDirParts = explode("/src", $_SERVER['DOCUMENT_ROOT']);
        if (empty($srcDirParts)) {
          echo 'Error: Can\'t determine root path.';
          exit(1);
        }

        $rootDir = $srcDirParts["0"];
        $filePath = realpath($rootDir . mb_substr($this->res["filePath"], 1));
        $this->res["filePath"] = $filePath;
      }

      $fileInfo = pathinfo($this->res["filePath"]);
      $extension = "." . $fileInfo['extension'];
      header("Content-Type: " . $this->getMime($extension));

      $file = fopen($this->res["filePath"], 'rb');
      while (!feof($file)) {
        echo fread($file, 1024 * 8); //$this->opts.TRANSMITTER_BLOCK_SIZE_KB
      }

      fclose($file);
      exit(0);
    }

    echo $this->res["body"];
    exit(0);
  }

  /**
   * setCode
   *
   * @param int $code
   * @return void
   */
  public function setCode($code): void {
    if (!is_numeric($code)) {
      echo 'Error: $code must be a number.';
      exit(1);
    }

    $this->res["code"] = $code;
  }

  /**
   * setFile
   *
   * @param  string $filePath
   * @param  bool $isStatic
   * @return void
   */
  public function setFile($filePath, $isStatic = false): void {
    $isFilePathValid = is_string($filePath);
    if (!$isFilePathValid) {
      echo 'Error: $filePath must be a string.';
      exit(1);
    }

    $isIsStaticValid = is_bool($isStatic);
    if (!$isIsStaticValid) {
      echo 'Error: $isStatic must be a boolean.';
      exit(1);
    }

    if (mb_strlen($this->res["body"])) {
      echo 'Error: Can\'t add an attachment to a Response that contains a body.';
      exit(1);
    }

    $this->res["filePath"] = $filePath;
    $this->res["isStatic"] = $isStatic;
  }

  /**
   * setHeader
   *
   * @param  string $key
   * @param  string $value
   * @return void
   */
  public function setHeader($key, $value): void {
    $isKeyValid = is_string($key);
    if (!$isKeyValid) {
      echo 'Error: header key must be a string.';
      exit(1);
    }

    $isValueValid = is_string($value);
    if (!$isValueValid) {
      echo 'Error: header value must be a string';
      exit(1);
    }

    $this->res["header"][$key] = $value;
  }
}
