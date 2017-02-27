@php
    use App\Models\Operations\Operation;
    use App\Models\Missions\Mission;
@endphp

<script>
    $(document).ready(function(e) {
        $('.mission-item').click(function(event) {
            var caller = $(this);
            var id = caller.data('id');

            if (caller.hasClass('spinner')) {
                event.preventDefault();
                return;
            }
            
            $.ajax({
                type: 'POST',
                url: '{{ url('/hub/missions/show-mission') }}',
                data: {'id': id},
                beforeSend: function() {
                    caller.missionSpinner(true);
                },
                success: function(data) {
                    openBigWindow(data, 500, function() {
                        caller.missionSpinner(false);
                    });
                }
            });

            event.preventDefault();
        });
    });
</script>

<div class="missions-pinned">
    <div class="missions-pinned-headers">
        <h2>Next Operation &mdash; {{ Operation::nextWeek()->startsIn() }}</h2>
        <h2>Past Operation</h2>
    </div>

    <div class="missions-pinned-groups">
        <ul class="mission-group mission-group-pinned mission-group-center">
            @foreach (Operation::nextWeek()->missions as $item)
                @include('missions.mission-item', ['mission' => $item->mission])
            @endforeach
        </ul>

        <ul class="mission-group mission-group-pinned mission-group-center">
            @foreach (Operation::lastWeek()->missions as $item)
                @include('missions.mission-item', ['mission' => $item->mission])
            @endforeach
        </ul>
    </div>
</div>

<h2 class="mission-section-heading">My Missions</h2>

<ul class="mission-group">
    @foreach (auth()->user()->missions() as $mission)
        @include('missions.mission-item', [
            'mission' => $mission,
            'ignore_new_banner' => true
        ])
    @endforeach
</ul>

<h2 class="mission-section-heading">New Missions</h2>

<ul class="mission-group">
    @foreach (Mission::allNew() as $mission)
        @include('missions.mission-item', ['mission' => $mission])
    @endforeach
</ul>

<h2 class="mission-section-heading">Past Missions</h2>

<ul class="mission-group">
    @foreach (Mission::allPast() as $mission)
        @include('missions.mission-item', ['mission' => $mission])
    @endforeach
</ul>
