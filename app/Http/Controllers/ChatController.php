<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

// class ChatController extends Controller
// {
//     public function sendMessage(Request $request)
//     {
//         $request->validate([
//             'message' => 'required|string'
//         ]);

//         $client = new Client();
        
//         try {
//             $response = $client->post('https://openrouter.ai/api/v1/chat/completions', [
//                 'headers' => [
//                     'Authorization' => 'Bearer '.config('services.openrouter.key'),
//                     'Content-Type' => 'application/json',
//                 ],
//                 'json' => [
//                     'model' => 'deepseek/deepseek-r1:free',
//                     'messages' => [
//                         ['role' => 'user', 'content' => $request->message]
//                     ]
//                 ]
//             ]);

//             $responseData = json_decode($response->getBody(), true);
//             return response()->json([
//                 'response' => $responseData['choices'][0]['message']['content'] ?? 'No response'
//             ]);

//         } catch (\Exception $e) {
//             return response()->json(['error' => $e->getMessage()], 500);
//         }
//     }
// }



class ChatController extends Controller
{
    // Predefined prompts and their custom answers
    protected $allowedPrompts = [
        'hi' => 'Hello! How can I assist you with your study abroad plans today?',
        'what is rohini international education services?' =>
            'Rohini International Education Services is a Nepal-based consultancy specializing in guiding students who want to study in New Zealand.',
        'where is rohini international located?' =>
            'We are located at House #160, Adwait Marg, Shankardev Campus Road, Putalisadak, Kathmandu. We also have branches in Birtamode, Birgunj, Damak, Chitwan, and Pokhara.',
        'how can i contact support?' =>
            'You can contact us at +977 1-5344710 or email info@rohini.edu.np. We are available Sunday to Friday, 09:00 AM to 05:00 PM.',
        'what services do you provide?' =>
            'We provide counseling, university and course selection, visa application support, documentation help, pre-departure briefings, financial and scholarship guidance, and accommodation support for students going to New Zealand.',
        'do you offer test preparation?' =>
            'Yes, we offer IELTS and PTE test preparation classes with experienced instructors and flexible schedules.',
        'why choose new zealand for higher education?' =>
            'New Zealand offers globally recognized qualifications, high-quality education, a safe environment, and post-study work opportunities.',
        'what is the process to apply to study in new zealand?' =>
            'We guide students through selecting a course, checking eligibility, gathering documents, applying for a visa, and preparing for departure.',
        'are there work opportunities in new zealand for students?' =>
            'Yes, students can work part-time during study, full-time during holidays, and apply for post-study work visas after graduation.',
        'what accommodation options are available in new zealand?' =>
            'Students can choose from shared flats, homestays, hostels, or on-campus housing. We help you find the right option.',
        'do you support nepalese students in new zealand?' =>
            'Absolutely! We provide full pre-departure briefings and continuous support to ensure students feel at home in New Zealand.'
    ];

    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string'
        ]);

        $userMessage = strtolower(trim($request->message)); // Normalize case

        // Direct match
        if (array_key_exists($userMessage, $this->allowedPrompts)) {
            return response()->json([
                'response' => $this->allowedPrompts[$userMessage]
            ]);
        }

        // Approximate keyword matching
        foreach ($this->allowedPrompts as $prompt => $answer) {
            similar_text($userMessage, $prompt, $percent);
            if ($percent > 80) {
                return response()->json([
                    'response' => "Did you mean: \"$prompt\"?\n" . $answer
                ]);
            }
        }

        // Fallback to OpenAI via OpenRouter
        try {
            $client = new Client();

            $response = $client->post('https://openrouter.ai/api/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . config('services.openrouter.key'),
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'deepseek/deepseek-r1:free',
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are a helpful education consultancy assistant. Answer only if the question is about Rohini International Education Services or studying in New Zealand.'],
                        ['role' => 'user', 'content' => $request->message]
                    ]
                ]
            ]);

            $responseData = json_decode($response->getBody(), true);

            return response()->json([
                'response' => $responseData['choices'][0]['message']['content'] ?? 'No response from assistant.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'response' => 'Sorry, I can only answer specific questions related to our consultancy. Please ask one of the allowed questions.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
