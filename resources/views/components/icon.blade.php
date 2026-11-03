@props(['name', 'class' => 'w-4 h-4', 'strokeWidth' => '2'])

<i data-lucide="{{ $name }}" {{ $attributes->merge(['class' => $class]) }} data-lucide-stroke-width="{{ $strokeWidth }}"></i>
