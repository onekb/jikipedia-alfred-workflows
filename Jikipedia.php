<?php
require_once('workflows.php');
require_once('vendor/autoload.php');

// curl 'https://api.jikipedia.com/go/search_definitions' \
//   -H 'Client: web' \
//   -H 'Content-Type: application/json;charset=UTF-8' \
//   --data-binary '{"phrase":"xswl","page":1}' \
//   --compressed

class Jikipedia
{
    protected $workflows;
    protected $client;
    public function __construct()
    {
        $this->workflows = new Workflows();
        $this->client = new \GuzzleHttp\Client([
            'base_uri' => 'https://api.jikipedia.com/'
        ]);
    }
    public function select($key)
    {
        $data = $this->search($key);
        foreach ($data as $value) {
            $tags = [];
            foreach ($value['tags'] as $tag) {
                $tags[] = $tag['name'];
            }

            $plaintext = str_replace(["\r\n", "\r", "\n"], "", $value['plaintext']);

            $this->workflows->result(
                $value['id'],
                $value['id'],
                $plaintext,
                '[' . $value['term']['title'] . ']  ' . join(' | ', $tags),
                "icon.png"
            );
        }
        echo $this->workflows->toxml();
    }

    private function search($key)
    {
        $content = $this->getRemoteData($key);
        $json = json_decode($content, true);
        return $json['data'];
    }

    private function getRemoteData($key)
    {
        $response = $this->client->request('POST', 'go/search_definitions', [
            'json' => [
                "phrase" => $key, "page" => 1
            ],
            'headers' => [
                'Client' => 'web',
                'Content-Type' => 'application/json;charset=UTF-8',
            ]
        ]);
        $content = $response->getBody()->getContents();
        return $content;
    }
}
