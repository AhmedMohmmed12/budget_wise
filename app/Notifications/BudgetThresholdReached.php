<?php

namespace App\Notifications;

use App\Support\BudgetPeriod;
use App\Support\Money;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BudgetThresholdReached extends Notification
{
    /**
     * @param  list<array{scope: string, name: ?string, percentage: float, spent: float, budget: float, threshold: int}>  $crossed
     */
    public function __construct(public BudgetPeriod $period, public array $crossed) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $noun = $this->period->isMonthly() ? $this->period->start->format('F') : 'this week\'s';
        $over = collect($this->crossed)->contains(fn ($c) => $c['threshold'] >= 100);

        $first = $this->crossed[0];
        $subject = count($this->crossed) === 1
            ? ($first['name'] ? "{$first['name']}: " : '').($first['threshold'] >= 100 ? "you've reached your {$noun} budget" : "80% of your {$noun} budget is gone")
            : ($over ? "You've reached some of your {$noun} limits" : "You're getting close on your {$noun} budget");

        $mail = (new MailMessage)
            ->subject(ucfirst($subject))
            ->greeting('Hi '.strtok($notifiable->name, ' ').',')
            ->line('A quick heads-up from your budget for '.$this->period->label().':');

        foreach ($this->crossed as $c) {
            $mail->line(sprintf(
                '%s: %s of %s (%d%%)',
                $c['name'] ?? 'Total',
                Money::format($c['spent'], $notifiable->currency),
                Money::format($c['budget'], $notifiable->currency),
                round($c['percentage'])
            ));
        }

        return $mail
            ->line($over ? 'Nothing to panic about, but the rest of the '.$this->period->noun().' is worth keeping quiet.' : 'Still room left, just worth keeping an eye on.')
            ->action('Open BudgetWise', route('dashboard'))
            ->salutation('You can turn these emails off in Settings.');
    }
}
