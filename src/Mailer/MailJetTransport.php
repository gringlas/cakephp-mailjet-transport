<?php

namespace gringlas\MailJetTransport\Mailer;

use Cake\Core\Configure;
use Cake\Log\LogTrait;
use Cake\Mailer\AbstractTransport;
use Cake\Mailer\Email;
use Exception;
use Mailjet\Client;
use Mailjet\Resources;
use Psr\Log\LogLevel;


/**
 * MailJet Transporter
 *
 * Class MailJetTransport
 * @package App\Mailer
 */
class MailJetTransport extends AbstractTransport
{

    use LogTrait;

    /**
     * @var Client
     */
    private $MailJet;


    public function __construct(array $config)
    {
        $this->MailJet = new Client(
            Configure::read('MailJet.key'),
            Configure::read('MailJet.secret'),
            true,
            [
                'version' => 'v3.1'
            ]);
        parent::__construct($config);
    }


    public function send(Email $email)
    {
        $body = $this->buildBody($email);

        $response = $this->MailJet->post(Resources::$Email, [
            'body' => $body
        ]);
        if ($response->success()) {
            $this->log(sprintf('Send %s to %s',
                $email->getProfile()['TemplateID'],
                implode(',', $email->getTo())
            ),
                LogLevel::INFO,
                'mailJet'
            );
            return true;
        }
        $this->log(sprintf('Error sending %s to %s with message: %s using credentials: %s %s and Error: %s',
            $email->getProfile()['TemplateID'],
            key($email->getTo()),
            $response->getStatus() . $response->getReasonPhrase(),
            Configure::read('MailJet.key'),
            Configure::read('MailJet.secret'),
            json_encode($response->getBody())
        ),
            LogLevel::ERROR,
            'mailJet'
        );

        return false;
    }


    private function buildBody(Email $email)
    {
        $variables = [];
        if (isset($email->getViewVars()['MailJet'])) {
            $variables = $email->getViewVars()['MailJet'];
        }
        $to = [];
        foreach ($email->getTo() AS $mail => $name) {
            $to[] = [
                'Email' => $mail,
                'Name' => $name
            ];
        }

        # setTo
        $message = [
            'To' => $to,
        ];

        if (!empty($email->getBcc())) {
            $bcc = [];
            foreach ($email->getBcc() AS $mail) {
                $bcc[] = [
                    'Email' => $mail,
                    'Name' => $mail
                ];
            }
            $message['Bcc'] = $bcc;
        }

        $fromEmail = key($email->getFrom());
        $fromName = $email->getFrom()[$fromEmail];


        if (!empty($variables)) {
            $message['Variables'] = $variables;
            $message['TemplateLanguage'] = true;
        }
        // we are useing a template from MailJet, otherwise use Html and Text templates from cake
        if (isset($email->getProfile()['TemplateID'])) {
            $message['TemplateID'] = $email->getProfile()['TemplateID'];
            $message['TemplateLanguage'] = true;
        } else {
            $message['TextPart'] = $email->message('text');
            $message['HTMLPart'] = $email->message('html');
        }
        return [
            'Messages' => [
                $message
            ]
        ];
    }

}
