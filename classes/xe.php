<?php

class XE
{
    private $url = 'https://www.xe.com/api/protected/midmarket-converter/';

    private $bearer = 'bG9kZXN0YXI6cHVnc25heA=='; // Base64 encode de : lodestar:pugsnax

    private $headers = [
        'accept' => '*/*',
        'accept-language' => 'fr-FR,fr;q=0.6',
        'cache-control' => 'no-cache',
        'cookie' => 'xeConsentState={%22performance%22:true%2C%22marketing%22:true%2C%22compliance%22:false};',
        'pragma' => 'no-cache',
        'priority' => 'u=1, i',
        'sec-ch-ua' => '"Brave";v="137", "Chromium";v="137", "Not/A)Brand";v="24"',
        'sec-ch-ua-mobile' => '?0',
        'sec-ch-ua-platform' => '"Windows"',
        'sec-fetch-dest' => 'empty',
        'sec-fetch-mode' => 'cors',
        'sec-fetch-site' => 'same-origin',
        'sec-gpc' => '1',
        'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'
    ];

    static $_rates = [];

    public function __construct(array $parameters)
    {
        $this->setHeaders($parameters);
    }

    private function setHeaders($parameters){

        $this->headers['authorization'] = 'Basic ' . $this->bearer;
        $this->headers['referer'] = 'https://www.xe.com/currencyconverter/convert/?Amount=1&From='.$parameters['from_currency'].'&To='.$parameters['to_currency'];
    }

    private function getRates($parameters): array
    {
        if (!empty(self::$_rates) && isset(self::$_rates['timestamp']) && self::$_rates['timestamp'] > (time() - 2 * 3600)) {
            return self::$_rates;
        }

        $this->setHeaders($parameters);

        // Convert headers array to HTTP context format
        $headers = [];
        foreach ($this->headers as $key => $value) {
            $headers[] = $key . ': ' . $value;
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => implode("\r\n", $headers),
                'accept_encoding' => '',
            ]
        ]);

        $response = file_get_contents($this->url, false, $context);

        if ($response === false) {
            return ['error' => 'Failed to retrieve data'];
        }

        if(!$result = json_decode($response, true)) {
            return ['error' => 'Failed to parse response'];
        }

        if(empty($result['timestamp']) || empty($result['rates'])) {
            return ['error' => 'No rates or timestamp found'];
        }

        self::$_rates = $result;

        return self::$_rates;
    }

    public function getConversion(array $parameters)
    {
        $rates = $this->getRates($parameters);

        $fromCurrency = $parameters['from_currency'];
        $toCurrency = $parameters['to_currency'];
        
        // Vérifier si EUR existe dans les taux
        if (!isset($rates['rates']['EUR'])) {
            return ['error' => 'EUR non trouvé dans les taux'];
        }
        
        // Obtenir le taux EUR/USD (combien d'USD pour 1 EUR)
        $eurToUsdRate = $rates['rates']['EUR'];
        
        // Calculer les taux par rapport à l'euro
        $eurBasedRates = [];

        foreach ($rates['rates'] as $currency => $usdRate) {
            if ($currency === 'EUR') {
                // EUR par rapport à EUR = 1
                $eurBasedRates[$currency] = 1.0;
            } else {
                // Conversion: eurToUsdRate / usdRate
                $eurBasedRates[$currency] = $eurToUsdRate / $usdRate;
            }
        }
        
        // Calculer la conversion spécifique demandée
        if (!isset($eurBasedRates[$fromCurrency]) || !isset($eurBasedRates[$toCurrency])) {
            return ['error' => 'Devise non trouvée dans les taux'];
        }
        
        // Conversion de fromCurrency vers toCurrency via EUR
        // (fromCurrency -> EUR) * (EUR -> toCurrency)
        $conversionRate = (1 / $eurBasedRates[$fromCurrency]) * $eurBasedRates[$toCurrency];
        
        return [
            'from_currency' => $fromCurrency,
            'to_currency' => $toCurrency,
            'rate' => $conversionRate,
            // 'eur_based_rates' => $eurBasedRates
        ];
    }

    public function getAllRates($parameters = [])
    {
        if(empty($parameters)) {
            $parameters = [
                'from_currency' => 'EUR',
                'to_currency' => 'USD'
            ];
        }
        $rates = $this->getRates($parameters);
        return $rates;
    }
}