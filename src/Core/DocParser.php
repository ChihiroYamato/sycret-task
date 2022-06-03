<?php

namespace App\Core;

use ConvertApi;

/**
 * **DocParser** -- класс парсер xml документов
 */
class DocParser
{
    /**
     * @var string `private` путь к апи GENDOC
     */
    private const API_GENDOC = 'https://sycret.ru/service/apigendoc/apigendoc';

    /**
     * @var \DOMDocument $xml `private` загруженный
     */
    private \DOMDocument $xml;

    /**
     * @var null|\CurlHandle $curl `private` активная сессия с апи GENDOC
     */
    private $curl;

    /**
     * Инициализация объекта класса
     * @param string $xmlTempate путь к шаблону xml
     * @return void
     */
    public function __construct(string $xmlTempate)
    {
        $xmlDocument = new \DOMDocument();
        $xmlDocument->loadXML($xmlTempate);

        $this->xml = $xmlDocument;
        $this->curl = null;
    }

    /**
     * **Method** производит замену текста тегов use > text на текст из запроса к апи GENDOC
     * @param int $recordid
     * @return \App\Core\DocParser инстанс текущего объекта
     */
    public function replace(int $recordid) : DocParser
    {
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

            $response = $this->fetch(['use' =>$useTag, 'text' => $textTag, 'recordid' => $recordid]);

            foreach ($text->getElementsByTagName('t') as $item) {
                $item->nodeValue = $response['resultdata'] ?? '';
            }
        }

        return $this;
    }

    /**
     * **Method** сохраняет текущий xml документ в файлы doc и pdf
     * @return array возвращает массив url к файлам
     */
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

    /**
     * **Method** производит запрос к апи GENDOC
     * @param array $requestParams параметры запроса
     * @return array заскодированный массив тела ответа
     */
    private function fetch(array $requestParams) : array
    {
        if ($this->curl === null) {
            $this->curl = curl_init(self::API_GENDOC);
            curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($this->curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
            curl_setopt($this->curl, CURLOPT_POST, true);
        }

        curl_setopt($this->curl, CURLOPT_POSTFIELDS, json_encode($requestParams, JSON_FORCE_OBJECT));
        $response = curl_exec($this->curl);

        if ($response === false) {
            throw new \Exception('Error with request to ' . self::API_GENDOC);
        }

        return json_decode($response, true);
    }
}
