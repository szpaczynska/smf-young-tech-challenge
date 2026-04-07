<?php

namespace App\Services;
use Illuminate\Support\Facades\Http;

class OcrAiParser
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

   public function parse(string $text): ?array
    {
        $prompt = "Wyciągnij kolor i typ ubrania w formacie JSON z podanego tekstu. Przykład JSON: {\"color\": \"czerwona\", \"type\": \"sukienka\"} Tekst: $text";
        $model = "google/flan-t5-base";

       $response = Http::withToken(env('HF_API_KEY'))
        ->withOptions(['verify' => false]) 
        ->timeout(30)
        ->post("https://api-inference.huggingface.co/models/$model", [
            'inputs' => $prompt
        ]);

        if ($response->failed()) {
            \Log::error('HF API error', ['body' => $response->body()]);
            return null;
        }

        $output = $response->json();
        $textResponse = is_array($output) && isset($output[0]['generated_text'])
            ? $output[0]['generated_text']
            : (is_string($output) ? $output : '');

        return $this->extractJson($textResponse);
    }

    private function extractJson(string $text): ?array
    {
        if (preg_match('/\{.*\}/s', $text, $matches)) {
            $json = $matches[0];
            $json = preg_replace('/(\w+)\s*:/', '"$1":', $json);
            $json = preg_replace('/:\s*([a-zA-Ząęćłńóśżź]+)/', ':"$1"', $json);

            return json_decode($json, true);
        }

        return null;
    }
}
