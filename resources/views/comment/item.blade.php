<article class="cell grid-x
    @if ($alignRight)
        align-right text-right
    @endif
">
    <p class="cell">
        {!! $comment->body !!}
    </p>

    <footer class="cell grid-x">
        <p class="cell">
            {{ $comment->creator->name }}
        </p>

        <p class="cell">
            {{ $comment->created_at }}
        </p>
    </footer>
</article>
