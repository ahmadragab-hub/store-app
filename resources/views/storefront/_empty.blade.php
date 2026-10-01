<div class="store-empty">
    @if (! empty($title))
        <p class="store-empty__title">{{ $title }}</p>
    @endif
    @if (! empty($message))
        <p class="store-empty__message">{{ $message }}</p>
    @endif
    @if (! empty($actionUrl) && ! empty($actionLabel))
        <a href="{{ $actionUrl }}" class="store-button-inline store-empty__action">{{ $actionLabel }}</a>
    @endif
</div>
