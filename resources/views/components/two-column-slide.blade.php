<div {{ $attributes->class([$wrapperClass()]) }}>
    <div class="{{ $leftClass() }}">{{ $left ?? '' }}</div>
    <div class="{{ $rightClass() }}">{{ $right ?? '' }}</div>
</div>
