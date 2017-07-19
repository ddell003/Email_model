# Email_model
Email model for codeigniter

Need to set up the following tables:

```
CREATE TABLE `email_queue` (
  `id` int(10) UNSIGNED NOT NULL,
  `to` text COLLATE utf8_unicode_ci NOT NULL,
  `bcc` text COLLATE utf8_unicode_ci NOT NULL,
  `subject` text COLLATE utf8_unicode_ci NOT NULL,
  `message` blob NOT NULL,
  `headers` blob NOT NULL,
  `data` mediumblob NOT NULL,
  `failed` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
```
AND 
```
CREATE TABLE `email_archive` (
  `id` int(10) UNSIGNED NOT NULL,
  `to` text COLLATE utf8_unicode_ci NOT NULL,
  `bcc` text COLLATE utf8_unicode_ci NOT NULL,
  `subject` text COLLATE utf8_unicode_ci NOT NULL,
  `message` blob NOT NULL,
  `headers` blob NOT NULL,
  `data` mediumblob NOT NULL,
  `failed` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
```

Push to the model:

```
$to = 'someEmail@gmail.com';
$headers  = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
$headers .= 'From: no-reply@me.net'."\r\n";

//email subject
$subject = count($expiring_contracts).' Expiring Contracts';
$message = $this->load->view('templates/emails/expiring_contracts', array('expiring_contracts' => $expiring_contracts), true);

$this->email_model->push($to, $subject, $message, $headers);
```

## Make cron job that hits

```
public function process_email() {

    return $this->email_model->process_queue(30);
}

```


