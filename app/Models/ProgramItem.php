<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgramItem extends Model
{
    use HasFactory;

    public const KIND_CATEGORY_PROGRAM = 'category_program';

    public const KIND_ROUTINE = 'routine';

    public const RECURRENCE_ONCE = 'once';

    public const RECURRENCE_DAILY = 'daily';

    public const CONTENT_TEXT = 'text';

    public const CONTENT_VIDEO = 'video';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'assigned_by',
        'category_id',
        'kind',
        'recurrence_type',
        'title',
        'content_type',
        'content_body',
        'video_url',
        'scheduled_date',
        'starts_on',
        'ends_on',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
            'starts_on' => 'date',
            'ends_on' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function completions(): HasMany
    {
        return $this->hasMany(ProgramCompletion::class);
    }

    public function scopeForUser(Builder $query, User|int $user): Builder
    {
        return $query->where('user_id', $user instanceof User ? $user->id : $user);
    }

    public function scopeDueOnDate(Builder $query, CarbonInterface|string $date): Builder
    {
        $resolvedDate = Carbon::parse($date)->toDateString();

        return $query->where(function (Builder $builder) use ($resolvedDate): void {
            $builder
                ->where(function (Builder $onceQuery) use ($resolvedDate): void {
                    $onceQuery
                        ->where('recurrence_type', self::RECURRENCE_ONCE)
                        ->whereDate('scheduled_date', $resolvedDate);
                })
                ->orWhere(function (Builder $dailyQuery) use ($resolvedDate): void {
                    $dailyQuery
                        ->where('recurrence_type', self::RECURRENCE_DAILY)
                        ->whereDate('starts_on', '<=', $resolvedDate)
                        ->where(function (Builder $rangeQuery) use ($resolvedDate): void {
                            $rangeQuery
                                ->whereNull('ends_on')
                                ->orWhereDate('ends_on', '>=', $resolvedDate);
                        });
                });
        });
    }

    public function isDueOn(CarbonInterface|string $date): bool
    {
        $resolvedDate = Carbon::parse($date)->toDateString();

        if (! $this->is_active) {
            return false;
        }

        if ($this->recurrence_type === self::RECURRENCE_ONCE) {
            return optional($this->scheduled_date)->toDateString() === $resolvedDate;
        }

        if ($this->recurrence_type === self::RECURRENCE_DAILY && $this->starts_on !== null) {
            if ($this->starts_on->toDateString() > $resolvedDate) {
                return false;
            }

            return $this->ends_on === null || $this->ends_on->toDateString() >= $resolvedDate;
        }

        return false;
    }

    public function completionForDate(CarbonInterface|string $date): ?ProgramCompletion
    {
        $resolvedDate = Carbon::parse($date)->toDateString();

        if ($this->relationLoaded('completions')) {
            return $this->completions
                ->first(fn (ProgramCompletion $completion) => $completion->completion_date->toDateString() === $resolvedDate);
        }

        return $this->completions()
            ->whereDate('completion_date', $resolvedDate)
            ->first();
    }

    public function isCompletedOn(CarbonInterface|string $date): bool
    {
        return $this->completionForDate($date) !== null;
    }

    public function getVideoProviderAttribute(): ?string
    {
        if (blank($this->video_url)) {
            return null;
        }

        $host = strtolower((string) parse_url($this->video_url, PHP_URL_HOST));
        $path = strtolower((string) parse_url($this->video_url, PHP_URL_PATH));

        if (str_contains($host, 'youtube.com') || str_contains($host, 'youtu.be')) {
            return 'youtube';
        }

        if (str_contains($host, 'vimeo.com')) {
            return 'vimeo';
        }

        if (preg_match('/\.(mp4|mov|webm)$/', $path) === 1) {
            return 'file';
        }

        return 'link';
    }

    public function getVideoEmbedUrlAttribute(): ?string
    {
        if ($this->video_provider === 'youtube') {
            $query = [];
            parse_str((string) parse_url($this->video_url, PHP_URL_QUERY), $query);

            $videoId = $query['v'] ?? null;

            if ($videoId === null && str_contains((string) parse_url($this->video_url, PHP_URL_HOST), 'youtu.be')) {
                $videoId = trim((string) parse_url($this->video_url, PHP_URL_PATH), '/');
            }

            return $videoId ? 'https://www.youtube.com/embed/'.$videoId : null;
        }

        if ($this->video_provider === 'vimeo') {
            $videoId = trim((string) parse_url($this->video_url, PHP_URL_PATH), '/');

            return $videoId !== '' ? 'https://player.vimeo.com/video/'.$videoId : null;
        }

        return null;
    }
}
