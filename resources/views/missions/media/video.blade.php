<span>
    <iframe src="{{ $video->url() }}" 
            frameborder="0" 
            allowfullscreen="true" 
            scrolling="no" 
            height="520" 
            width="520">
    </iframe>

    @if ($video->isMine() || auth()->user()->can('delete-media'))
            <span class="fa fa-trash mission-video-item-delete" data-video="{{ $video->id }}"></span>
    @endif
</span>