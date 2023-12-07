<?php
namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Network\Http\Client;

class ChatController extends Controller
{
    public function chat()
    {
        $this->viewBuilder()->autoLayout(false);
        $this->loadComponent('RequestHandler');

        $userInput = $this->request->getData('user_input');

        // You may want to customize the system message based on your needs
        $systemMessage = ['role' => 'system', 'content' => 'You are a helpful assistant.'];

        // Send user input and system message to ChatGPT API
        $apiUrl = 'https://api.openai.com/v1/chat/completions';
        $apiKey = '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'; // Replace with your actual API key

        $http = new Client();
        $response = $http->post($apiUrl, json_encode([
            'model' => 'gpt-3.5-turbo',
            'messages' => [$systemMessage, ['role' => 'user', 'content' => $userInput]]
        ]), [
            'headers' => ['Authorization' => 'Bearer ' . $apiKey, 'Content-Type' => 'application/json'],
        ]);
        // debug($response->json);exit;

        $generatedText = '';
        if (isset($response->json['choices'])) {
            $generatedText = $response->json['choices'][0]['message']['content'];
        }
        
        // Check if the generated text indicates a request for a report
        if (strpos(strtolower($generatedText), 'monthly report') !== false) {
            // Query your site's data to get the desired report
            $mostResignedStaff = $this->getMostResignedStaffInMonths(); // Implement this method

            // Display the report information
            $this->set('reportInfo', $mostResignedStaff);
        } else {
            // Handle other responses or provide a default message
            $this->set('reportInfo', 'Sorry, I couldn\'t generate the requested report.');
        }

        $this->set(compact('userInput', 'generatedText', 'reportInfo'));
        $this->set('_serialize', ['userInput', 'generatedText', 'reportInfo']);
    }

    // Method to get the most resigned staff in the last few months (replace with your actual logic)
    private function getMostResignedStaffInMonths()
    {
        $this->loadModel('Users');
        $users = $this->Users->find('all', [
            'order' => ['Users.id' => 'DESC'],
            'limit' => 5
        ]);
        
        $html = '';
        $html .= '<table class="table">';
            $html .='<thead>';
                $html .='<tr>';
                    $html .='<th>Full Name</th>';
                    $html .='<th>Resgined Date</th>';
                $html .='</tr>';
            $html .='</thead>';
            $html .='<tbody>';
                foreach ($users as $user) { 
                    $html .='<tr>';
                        $html .= '<td>'.$user->first_name.' '.$user->last_name.'</td>';
                        $html .= '<td>'.$user->created.'</td>';
                    $html .='</tr>';
                }
            $html .='</tbody>';
        $html .='</table>';
        return $html;
        // Implement your logic to query the database or any other data source
        // and return the report information
        // Example: return $this->YourModel->findMostResignedStaffInMonths();
        // return 'Your report information here.';
    }
}