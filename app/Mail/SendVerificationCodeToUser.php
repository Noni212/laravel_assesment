<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendVerificationCodeToUser extends Mailable
{
    use Queueable, SerializesModels;
    public $pin_code;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($pin_code)
    {
        $this->pin_code = $pin_code;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('email.sendVerificationCode');
    }
}
