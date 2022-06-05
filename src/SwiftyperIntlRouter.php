<?php

namespace Swiftyper\fbt;

class SwiftyperIntlRouter
{
    /**
     * @var string
     */
    private $fbtDir;

    /**
     * @var SwiftyperIntlService
     */
    private $service;

    /**
     * @var SwiftyperSignatureValidator
     */
    private $signatureValidator;

    public function __construct()
    {
        $root_app = dirname(__DIR__);

        if (! is_file($root_app . '/vendor/autoload.php')) {
            $root_app = dirname(__DIR__, 4);
        }

        $config = require($root_app . '/swiftyper_config.php');
        $this->fbtDir = $config['fbt']['path'] . '/';
        $this->service = new SwiftyperIntlService();
        $this->signatureValidator = new SwiftyperSignatureValidator($config);
    }

    public function sync()
    {
        if ($this->signatureValidator->isValid()) {
            $this->service->deploy($this->fbtDir);
            $this->service->upload($this->fbtDir);

            $this->response([]);
        }

        $this->abort();
    }

    public function upload()
    {
        if ($this->signatureValidator->isValid()) {
            $this->service->upload($this->fbtDir);

            $this->response([]);
        }

        $this->abort();
    }

    public function deploy()
    {
        if ($this->signatureValidator->isValid()) {
            $this->service->deploy($this->fbtDir);

            $this->response([]);
        }

        $this->abort();
    }

    private function response(array $data)
    {
        header('content-type: application/json');

        echo json_encode($data);

        exit();
    }

    private function abort()
    {
        http_response_code(403);
    }
}
