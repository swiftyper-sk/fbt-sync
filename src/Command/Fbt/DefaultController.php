<?php

namespace Swiftyper\fbt\Command\Fbt;

use Minicli\App;
use Minicli\Command\CommandCall;
use Minicli\Command\CommandController;
use Swiftyper\Exception\ApiErrorException;
use Swiftyper\Fbt;
use Swiftyper\fbt\SwiftyperIntlService;
use Swiftyper\Swiftyper;

class DefaultController extends CommandController
{
    /** @var array */
    protected $config = [];

    /**
     * @var SwiftyperIntlService
     */
    private $service;

    /**
     * Cache storage path for generated translations & source strings.
     *
     * @var string
     */
    private $fbtDir;

    public function boot(App $app)
    {
        parent::boot($app);

        $this->config = $this->getApp()->config;
        $this->service = new SwiftyperIntlService();
    }

    public function handle()
    {
        $this->fbtDir = $this->config->fbt['path'] . '/';

        if (! is_dir($this->fbtDir)) {
            mkdir($this->fbtDir, 0755, true);
        }

        Swiftyper::setApiKey($this->config->api_key);

        try {
            if ($this->hasFlag('--config')) {
                $this->config();
            } elseif ($this->hasFlag('--init')) {
                $this->init();
            } elseif ($this->hasFlag('--deploy')) {
                $this->deploy();
            } elseif ($this->hasFlag('--upload')) {
                $this->upload($this->getParam('--upload'));
            } else {
                throw new \Exception('Invalid call, try to run php ./vendor/bin/swiftyper fbt --init');
            }
        } catch (\Exception $e) {
            $this->getPrinter()->error($e->getMessage());
        }
    }

    private function config()
    {
        $path = $this->config->path . '/swiftyper_config.php';

        if (file_exists($path)) {
            $this->getPrinter()->error('Config file already published in ' . $path);
        } else {
            copy(__DIR__ . '/../../../config_sample.php', $path);

            $this->getPrinter()->success('Config file published to ' . $path);
            $this->getPrinter()->info('Now you can set the API key, FBT storage path or other options.');
            $this->getPrinter()->error('Remember that you will not be able to change the FBT settings later (like hash_module, md5_digest).');
        }
    }

    /**
     * @throws ApiErrorException
     */
    private function init()
    {
        $this->requireConfig();

        $this->getPrinter()->info('🚀  Initializing project...');

        Fbt::initialize([
            'platform' => 'php',
            'version' => \Composer\InstalledVersions::getVersion('richarddobron/fbt'),
            'hash_module' => $this->config->fbt['hash_module'],
            'md5_digest' => $this->config->fbt['md5_digest'],
        ]);

        $this->getPrinter()->success('Project is prepared.');

        $this->run(new CommandCall(['fbt', '--upload']));
    }

    /**
     * @param string|null $path
     * @throws ApiErrorException
     * @throws \Exception
     */
    private function upload($path)
    {
        $this->requireConfig();

        $this->getPrinter()->info('⚡  Uploading phrases...');

        $this->printResponse($this->service->upload($path ?: $this->fbtDir));
    }

    /**
     * @throws ApiErrorException
     * @throws \Exception
     */
    private function deploy()
    {
        $this->requireConfig();

        $this->getPrinter()->info('👽  Translating app...');

        $flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
        if ($this->hasFlag('--pretty')) {
            $flags |= JSON_PRETTY_PRINT;
        }

        $this->printResponse($this->service->deploy($this->fbtDir, $flags));
    }

    /**
     * @throws \Exception
     */
    private function requireConfig()
    {
        $path = $this->config->path . '/swiftyper_config.php';

        if (! file_exists($path)) {
            throw new \Exception(
                'The config file does not exist.'
                . PHP_EOL
                . PHP_EOL
                . 'Run php ./vendor/bin/swiftyper --config'
            );
        }
    }

    private function printResponse(array $response)
    {
        foreach ($response['errors'] as $error) {
            $this->getPrinter()->error($error);
        }

        foreach ($response['info'] as $info) {
            $this->getPrinter()->info($info);
        }
    }
}
