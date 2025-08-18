<script setup lang="ts">
interface BadgeProps {
  variant?: 'default' | 'secondary' | 'success' | 'destructive' | 'outline'
  class?: string
}

const props = withDefaults(defineProps<BadgeProps>(), {
  variant: 'default'
})

const badgeClasses = {
  default: 'bg-primary text-primary-foreground hover:bg-primary/80',
  secondary: 'bg-secondary text-secondary-foreground hover:bg-secondary/80', 
  success: 'bg-green-100 text-green-800',
  destructive: 'bg-destructive text-destructive-foreground hover:bg-destructive/80',
  outline: 'border border-input bg-background hover:bg-accent hover:text-accent-foreground'
}
</script>

<template>
  <span 
    :class="[
      'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold transition-colors',
      badgeClasses[variant],
      props.class
    ]"
  >
    <slot />
  </span>
</template>