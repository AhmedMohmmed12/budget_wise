{{-- Colour picker bound to the parent's Alpine "color". Expects $id and $colors. --}}
<fieldset>
    <legend class="label">Colour</legend>
    <div class="flex flex-wrap gap-1.5 py-1">
        @foreach ($colors as $swatch)
            <label class="cursor-pointer">
                <input type="radio" name="color" value="{{ $swatch }}" x-model="color" class="peer sr-only">
                <span class="block h-6 w-6 rounded-full ring-offset-2 ring-offset-paper peer-checked:ring-2 peer-checked:ring-ink peer-focus-visible:ring-2 peer-focus-visible:ring-pine"
                      style="background: {{ $swatch }}" title="{{ $swatch }}"></span>
            </label>
        @endforeach
    </div>
</fieldset>
