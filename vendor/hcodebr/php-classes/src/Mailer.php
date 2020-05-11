<?php 

namespace Hcode;

use Rain\Tpl;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class Mailer {

	const USERNAME = "usuário";
	const PASSWORD = "senha";
	const NAME_FROM = "Nome da loja";

	private $mail; 

	// paramtros, endereço a ser enviado, nome do destinatário, assunto, arquivo de template, dados 
	public function __construct($toAddress, $toName, $subject, $tplName, $data = array())
	{

		$config = array(
		    "base_url"      => null,
		    "tpl_dir"       => $_SERVER['DOCUMENT_ROOT']."/views/email/", //caminho das paginas
		    "cache_dir"     => $_SERVER['DOCUMENT_ROOT']."/views-cache/", //caminho para o cache
		    "debug"         => false
		);

		Tpl::configure( $config );

		$tpl = new Tpl; //instacia a classe

		foreach ($data as $key => $value) {
			$tpl->assign($key, $value);
		}

		$html = $tpl->draw($tplName, true);

		$this->mail = new \PHPMailer;

		$this->mail->isSMTP();
		$this->mail->SMTPOptions = array(
		    'ssl' => array(
		        'verify_peer' => false,
		        'verify_peer_name' => false,
		        'allow_self_signed' => true
		    )
		);

		//Tell PHPMailer to use SMTP
		$this->mail->isSMTP();

		//Enable SMTP debugging
		// SMTP::DEBUG_OFF = off (for production use)
		// SMTP::DEBUG_CLIENT = client messages
		// SMTP::DEBUG_SERVER = client and server messages
		$this->mail->SMTPDebug = 0;

		//Set the hostname of the mail server
		$this->mail->Host = 'smtp.gmail.com';
		// use
		// $this->mail->Host = gethostbyname('smtp.gmail.com');
		// if your network does not support SMTP over IPv6

		//Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
		$this->mail->Port = 587;

		//Set the encryption mechanism to use - STARTTLS or SMTPS
		$this->mail->SMTPSecure = 'tls';

		//Whether to use SMTP authentication
		$this->mail->SMTPAuth = true;

		//Username to use for SMTP authentication - use full email address for gmail
		$this->mail->Username = Mailer::USERNAME;

		//Password to use for SMTP authentication
		$this->mail->Password = Mailer::PASSWORD;

		// Set who the message is to be sent from 
		$this->mail->setFrom(Mailer::USERNAME, Mailer::NAME_FROM);

		//Set an alternative reply-to address
		// $this->mail->addReplyTo('replyto@example.com', 'First Last');

		//Set who the message is to be sent to
		$this->mail->addAddress($toAddress, $toName); // email do destinatário e nome

		//Set the subject line
		$this->mail->Subject = $subject; // assunto

		//Read an HTML message body from an external file, convert referenced images to embedded,
		//convert HTML into a basic plain-text alternative body
		$this->mail->msgHTML($html); //mensagem

		//Replace the plain text body with one created manually
		$this->mail->AltBody = 'This is a plain-text message body';

		//Attach an image file aqui seria para anexo
		// $this->mail->addAttachment('images/phpmailer_mini.png');

		//send the message, check for errors
		
	}

	function save_mail($mail)
{
    //You can change 'Sent Mail' to any other folder or tag
    $path = '{imap.gmail.com:993/imap/ssl}[Gmail]/Sent Mail';

    //Tell your server to open an IMAP connection using the same username and password as you used for SMTP
    $imapStream = imap_open($path, $this->mail->Username, $this->mail->Password);

    $result = imap_append($imapStream, $path, $this->mail->getSentMIMEMessage());
    imap_close($imapStream);

    return $result;
}

	public function send()
	{

		return $this->mail->send();

	}

}

 ?>