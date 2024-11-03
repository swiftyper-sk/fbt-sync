<?php

namespace Swiftyper\fbt;

use Minicli\App;
use Minicli\ServiceInterface;
use Swiftyper\Phrase;
use Swiftyper\Translation;

class SwiftyperIntlService implements ServiceInterface
{
    /**
     * @var array
     */
    private $config;
    private $result = [
        'errors' => [],
        'info' => [],
    ];

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    public function load(App $app)
    {
    }

    /**
     * @throws \Swiftyper\Exception\ApiErrorException
     */
    public function upload(string $path): array
    {
        $phrases = $path . '/.source_strings.json';

        if (! file_exists($phrases)) {
            $this->result['errors'][] = 'Native phrases file (' . $phrases . ') not found!';
        } else {
            $swiftyper = Phrase::upload([
                'native_strings' => file_get_contents($phrases),
            ]);

            $this->result['info'][] = $swiftyper->saved . ' phrases has been stored.';
        }

        $translations = $path . '/.translations.json';

        if (! file_exists($translations)) {
            $this->result['errors'][] = 'Translations file (' . $translations . ') not found!';
        } else {
            $trans = file_get_contents($translations);
            if (! json_decode($trans)) {
                $this->result['errors'][] = 'Translations file is empty.';
            } else {
                $swiftyper = Translation::upload([
                    'translations' => $trans,
                ]);

                $this->result['info'][] = $swiftyper->translations . ' translations has been stored.';
            }
        }

        return $this->result;
    }

    /**
     * @throws \Swiftyper\Exception\ApiErrorException
     * @throws \Exception
     */
    public function deploy(string $path, int $flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE): array
    {
        $file = $path . '/translatedFbts.json';

        if (! is_dir($path) || ! is_writable($path)) {
            throw new \Exception("Directory $path is not writable.");
        } elseif (is_file($file) && ! is_writable($file)) {
            throw new \Exception("File $file is not writable.");
        }

        $translations = Translation::raw([
            'fallback' => $this->config['fallback'] ?? [],
        ]);

        file_put_contents($file, json_encode($translations, $flags));

        $this->result['info'][] = 'Translations has been deployed.';

        return $this->result;
    }
}
