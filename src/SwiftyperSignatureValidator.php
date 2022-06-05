<?php

namespace Swiftyper\fbt;

class SwiftyperSignatureValidator
{
    /**
     * @var array
     */
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function isValid(): bool
    {
        if (! empty($this->config['verify_signature'])) {
            return true;
        }

        $signatureHeaderContent = $_SERVER['HTTP_X_SWIFTYPER_SIGNATURE'] ?? null;
        $payload = file_get_contents("php://input");

        if (strpos($payload, "payload=") === 0) {
            $payload = substr(urldecode($payload), 8);
        }

        list($algo, $signature) = explode("=", $signatureHeaderContent);

        if ($algo !== 'sha256' || ! $signature) {
            return false;
        }

        $signingSecret = $this->config['api_key'];

        if (empty($signingSecret)) {
            return false;
        }

        $computedSignature = hash_hmac('sha256', $payload, $signingSecret);

        return hash_equals($signature, $computedSignature);
    }
}
