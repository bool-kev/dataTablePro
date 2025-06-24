@props([
    'status',
])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-medium text-sm text-[#F53003] dark:text-[#F61500] bg-[#fff2f2] dark:bg-[#1D0002] border border-[#F53003] dark:border-[#F61500] rounded-sm p-3']) }}>
        {{ $status }}
    </div>
@endif
