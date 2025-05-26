<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class ChatController extends Controller
{
    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string'
        ]);

        $client = new Client();
        
        try {
            $response = $client->post('https://openrouter.ai/api/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer '.config('services.openrouter.key'),
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'deepseek/deepseek-r1:free',
                    'messages' => [
                        ['role' => 'user', 'content' => $request->message]
                    ]
                ]
            ]);

            $responseData = json_decode($response->getBody(), true);
            return response()->json([
                'response' => $responseData['choices'][0]['message']['content'] ?? 'No response'
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}