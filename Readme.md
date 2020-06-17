# CakePHP 3 MailJet MailerTransport
This plugin provides a simple MailJet Transport for use with cakePHP 3.

# How To Use

It is recommended to use cakePHPs Mailer classes.
At first add the MailJetTransport to your `app.php`
```
'EmailTransport' => [
        'default' => [
            'className' => 'gringlas\MailJetTransport\Mailer\MailJetTransport',
        ]
    ],
```

Also add your credentials to your `app.php`
```
/**
     * MailJet credentials
     *
     * - key - MailJet Api key
     * - 'secret' - MailJet Api secret
     * - mailJetKeyInTemplateVars - if set to true templateVars from MailJet key will be taken and sent to MailJet,
     * otherwise send complete templateVars
     *
     */
    'MailJet' => [
        'key' => '1231sdasd',
        'secret' => 'asdasd123123',
        'mailJetKeyInTemplateVars' => false
    ],
```

To log responses from mailJet api please add logger to `app.php`
```
/**
* logging mailJet messages
*/
'mailJet' => [
    'className' => 'Cake\Log\Engine\FileLog',
    'path' => LOGS,
    'file' => 'mailJet',
    'url' => env('LOG_JETQUERIES_URL', null),
    'scopes' => ['mailJet']
],
```



In your email profiles use `TemplateID` for the MailJet transactional template. 

If you using `Email` in `app.php` :

```
'Email' => [

    'passwordreset' => [
        'TemplateID' => 1234,
        'subject' => 'Password Reset'
    ]
]
```


You can also add the `TemplateID` directly in a Mailer by calling `setProfile()`.
If `mailJetKeyInTemplateVars` is set, provode template vars with `setViewVars()` in `MailJet` key, otherwise simply all vars will be sent to MailJet: 

```
public function passwordreset() {
    return $this
        ->setProfile([
            'TemplateID' => 1234
        ])
        ->setViewVars([
            'newPassword' => 'a192ja',
            'MailJet' => [
                'newPassword' => 'a192ja'
            ]
        ])
        ->setSubject('Password reset');
}
```

Only TemplateVariables are getting sent to MailJet API. Subject, from, ... are all have to get configured in MailJet Templates. So you can easily switch back to cakePHPs default `MailTransport`.  
