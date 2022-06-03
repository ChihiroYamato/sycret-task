<?php

namespace App\Core;

use ConvertApi;

class DocParser
{
    private const API_GENDOC = 'https://sycret.ru/service/apigendoc/apigendoc';

    private \DOMDocument $xml;

    public function __construct(string $xmlTempate)
    {
        $xmlDocument = new \DOMDocument();
        $xmlDocument->loadXML($xmlTempate);

        $this->xml = $xmlDocument;
    }

    public function replace(int $recordid) : DocParser
    {
        $curl = curl_init(self::API_GENDOC);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($curl, CURLOPT_POST, true);

        foreach ($this->xml->getElementsByTagName('text') as $text) {
            $textTag = $text->nodeName;
            for ($i = 0; $i < $text->attributes->length; $i++) {
                $attribute = $text->attributes->item($i);

                $textTag .= sprintf(' %s="%s"', $attribute->nodeName, $attribute->nodeValue);
            }

            $use = $text;
            do {
                $use = $use->parentNode;
            } while ($use->localName !== 'use');

            $useTag = $use->nodeName;
            for ($i = 0; $i < $use->attributes->length; $i++) {
                $attribute = $use->attributes->item($i);

                $useTag .= sprintf(' %s="%s"', $attribute->nodeName, $attribute->nodeValue);
            }

            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(['use' =>$useTag, 'text' => $textTag, 'recordid' => $recordid], JSON_FORCE_OBJECT));
            $response = curl_exec($curl);

            if ($response === false) {
                throw new \Exception('Error with request to ' . self::API_GENDOC);
            }

            foreach ($text->getElementsByTagName('t') as $item) {
                $item->nodeValue = json_decode($response)->resultdata ?? '';
            }
        }

        return $this;
    }

    public function save() : array
    {
        $currentTime = (new \DateTime())->format('Y-m-d H:m:i');
        $doc = "/$currentTime.doc";
        $pdf = "/$currentTime.pdf";
        $serverUrl = "{$_SERVER['REQUEST_SCHEME']}://{$_SERVER['HTTP_HOST']}";

        $this->xml->save(SERVER_ROOT . $doc);

        ConvertApi\ConvertApi::setApiSecret($_ENV['CONVERT_API_SECRET']);
        $result = ConvertApi\ConvertApi::convert('pdf', ['File' => SERVER_ROOT . $doc], 'doc');
        $result->getFile()->save(SERVER_ROOT . $pdf);

        return [
            'URLWord' => "{$serverUrl}{$doc}",
            'URLPDF' => "{$serverUrl}{$pdf}"
        ];
    }
}
