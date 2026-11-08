@php
    use App\Enums\TaskStatus;

    $current = $tasks->first(fn ($task) => ! in_array($task->status, [TaskStatus::Selesai, TaskStatus::Dilewati], true));
@endphp

@if ($tasks->isEmpty())
    <p class="empty">Tahapan belum dibuat.</p>
@else
    <ol class="timeline">
        @foreach ($tasks as $task)
            @php
                $state = match (true) {
                    $task->status === TaskStatus::Selesai => 'done',
                    $task->status === TaskStatus::Dilewati => 'skipped',
                    $current && $task->id === $current->id => 'active',
                    default => 'todo',
                };
            @endphp

            <li class="timeline-item is-{{ $state }}">
                <div class="timeline-marker" aria-hidden="true"></div>
                <div class="timeline-body">
                    <p class="timeline-stage">{{ $task->stage->label() }}</p>
                    <p class="timeline-state">
                        @switch($state)
                            @case('done') Selesai @break
                            @case('skipped') Dilewati @break
                            @case('active') Sedang dikerjakan @break
                            @default Belum mulai
                        @endswitch
                    </p>
                    @if ($task->note)
                        <p class="timeline-note">{{ $task->note }}</p>
                    @endif
                </div>
            </li>
        @endforeach
    </ol>
@endif
