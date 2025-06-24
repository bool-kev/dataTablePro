@props([
    'title',
    'description',
])

<div class="flex w-full flex-col text-center">
    <h2 class="mb-1 font-medium text-[#1b1b18] dark:text-[#EDEDEC] text-lg">{{ $title }}</h2>
    <p class="mb-4 text-[#706f6c] dark:text-[#A1A09A] text-sm">{{ $description }}</p>
</div>
