<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ErrorReportNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected string $filePath;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('📌 Relatório de Erros no Sistema')
            ->greeting('Olá,')
            ->line('Ocorreram erros no sistema. O arquivo de logs está anexado.')
            ->line('Verifique o anexo para mais detalhes.')
            ->salutation('Atenciosamente, NexGen Software')
            ->attach($this->filePath, [
                'as' => 'error_log.json',
                'mime' => 'application/json',
            ]);
    }
}
