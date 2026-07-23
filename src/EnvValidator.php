<?php

namespace Eweaver\EnvValidator;

class EnvValidator
{
    private $envFile;
    private $exampleFile;

    public function __construct($envFile = '.env', $exampleFile = '.env.example')
    {
        $this->envFile = $envFile;
        $this->exampleFile = $exampleFile;
    }

    public function run()
    {
        if (!file_exists($this->exampleFile)) {
            echo "\033[31mError: {$this->exampleFile} not found.\033[0m\n";
            return 1;
        }

        if (!file_exists($this->envFile)) {
            echo "\033[33mWarning: {$this->envFile} not found. Creating from {$this->exampleFile}...\033[0m\n";
            copy($this->exampleFile, $this->envFile);
        }

        $exampleKeys = $this->parseKeys($this->exampleFile);
        $envKeys = $this->parseKeys($this->envFile);

        $missingKeys = array_diff($exampleKeys, $envKeys);

        if (empty($missingKeys)) {
            echo "\033[32mSuccess: Your .env file is up to date!\033[0m\n";
            return 0;
        }

        echo "\033[33mFound " . count($missingKeys) . " missing key(s) in your .env file.\033[0m\n\n";

        $handle = fopen("php://stdin", "r");

        foreach ($missingKeys as $key) {
            echo "Enter value for \033[36m{$key}\033[0m (leave blank to skip): ";
            $value = trim(fgets($handle));

            if ($value !== '') {
                // If value has spaces, wrap in quotes
                if (strpos($value, ' ') !== false) {
                    $value = '"' . $value . '"';
                }
                file_put_contents($this->envFile, "\n{$key}={$value}", FILE_APPEND);
                echo "\033[32mAdded {$key}\033[0m\n";
            } else {
                echo "\033[33mSkipped {$key}\033[0m\n";
            }
        }

        fclose($handle);

        echo "\n\033[32mValidation complete.\033[0m\n";
        return 0;
    }

    private function parseKeys($file)
    {
        $keys = [];
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);
            if (strpos($line, '#') === 0) {
                continue; // Skip comments
            }

            if (strpos($line, '=') !== false) {
                list($key) = explode('=', $line, 2);
                $keys[] = trim($key);
            }
        }

        return $keys;
    }
}
