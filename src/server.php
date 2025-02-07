<?php

require_once "routes.php";
require_once "core/boot/server/index.php";
require_once __DIR__ . "/../vendor/autoload.php";

/**
 * Server for Production
 * 
 * IMPORTANT:
 * 
 * Currently, this framework doesn't work with a real ArnelifyServer written in C and C++.
 * GitHub: https://github.com/arnelify/arnelify-server-cpp
 * 
 * It's only simulating the server functionality at the moment.
 * 
 * @return int
 */
function main(): int {

  $server = new ArnelifyServerExperimental([
    "SERVER_ALLOW_EMPTY_FILES" => true,
    "SERVER_BLOCK_SIZE_KB" => 1024,
    "SERVER_CHARSET" => "UTF-8",
    "SERVER_GZIP" => true,
    "SERVER_KEEP_EXTENSIONS" => true,
    "SERVER_MAX_FIELDS" => 100,
    "SERVER_MAX_FIELDS_SIZE_TOTAL_MB" => 50,
    "SERVER_MAX_FILES" => 10,
    "SERVER_MAX_FILES_SIZE_TOTAL_MB" => 100,
    "SERVER_MAX_FILE_SIZE_MB" => 10,
    "SERVER_PORT" => 3001,
    "SERVER_QUEUE_LIMIT" => 10,
    "SERVER_UPLOAD_DIR" => "./src/storage/upload"
  ]);

  $router = null;
  routes($router);
  $server->setHandler(function ($req, $res) use ($router) {
    
    //$router;

    $res->setCode(200);
    $res->addBody(json_encode($req));
    $res->end();
  });

  $server->start(function ($message, $isError) {
    if ($isError) {
      echo 'Error: ' . $message;
      return;
    }

    echo $message;
  });

  return 0;
}

main();