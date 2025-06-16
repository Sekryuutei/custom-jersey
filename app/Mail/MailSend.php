<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Sichikawa\LaravelSendgridDriver\SendGrid;

class MailSend extends Mailable
{
    use Queueable, SerializesModels, SendGrid;

    public $mail;

    /**
     * Create a new message instance.
     */
    public function __construct($mail)
    {
        $this->mail = $mail;
    }

    public function build()
    {
        return $this->subject(subject: 'Oceana Corporation')
                    ->view('mailtemplate');
    }

    public function attachments(): array
    {
        return [];
    }
}
