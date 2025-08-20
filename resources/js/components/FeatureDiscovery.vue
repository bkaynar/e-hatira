<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import 'intro.js/introjs.css';

interface Props {
    steps?: Array<{
        element: string;
        intro: string;
        position?: string;
    }>;
    autoStart?: boolean;
    onComplete?: () => void;
    onSkip?: () => void;
}

const props = withDefaults(defineProps<Props>(), {
    steps: () => [
        {
            element: '[data-intro="dashboard"]',
            intro: "Welcome to your dashboard! This is your control center where you can manage everything.",
            position: 'bottom'
        },
        {
            element: '[data-intro="events"]',
            intro: "Here you can create and manage your events. Click to add your first event!",
            position: 'right'
        },
        {
            element: '[data-intro="photos"]',
            intro: "Upload and organize photos for your events from this section.",
            position: 'left'
        },
        {
            element: '[data-intro="profile"]',
            intro: "Access your profile settings and account preferences here.",
            position: 'bottom'
        }
    ],
    autoStart: false
});

const isVisible = ref(false);

let introJs: any = null;

const startTour = async () => {
    if (typeof window === 'undefined') return;
    
    const { default: introjs } = await import('intro.js');
    introJs = introjs();
    
    introJs.setOptions({
        steps: props.steps,
        showStepNumbers: false,
        showBullets: false,
        exitOnOverlayClick: false,
        skipLabel: 'Skip Tour',
        nextLabel: 'Next',
        prevLabel: 'Previous',
        doneLabel: 'Get Started!'
    });
    
    introJs.onbeforechange(() => {
        const currentStep = introJs._currentStep;
        const step = props.steps[currentStep];
        if (step) {
            const element = document.querySelector(step.element);
            if (element) {
                element.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });
    
    introJs.oncomplete(() => {
        isVisible.value = false;
        markOnboardingCompleted();
        if (props.onComplete) {
            props.onComplete();
        }
    });
    
    introJs.onexit(() => {
        isVisible.value = false;
        markOnboardingCompleted();
        if (props.onSkip) {
            props.onSkip();
        }
    });
    
    isVisible.value = true;
    introJs.start();
};

const markOnboardingCompleted = async () => {
    try {
        await router.patch(route('user.complete-onboarding'));
    } catch (error) {
        console.error('Failed to mark onboarding as completed:', error);
    }
};

onMounted(() => {
    if (props.autoStart) {
        setTimeout(() => {
            startTour();
        }, 1000);
    }
});

defineExpose({
    startTour
});
</script>

<template>
    <div v-if="isVisible" class="feature-discovery-overlay">
        <!-- Optional custom overlay styling -->
    </div>
</template>

<style>
.introjs-tooltip {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

.introjs-tooltiptext {
    color: #1f2937;
    font-size: 1rem;
    line-height: 1.625;
}

.introjs-button {
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    font-weight: 500;
    transition: all 0.15s ease-in-out;
}

.introjs-nextbutton {
    background-color: #2563eb;
    color: white;
}

.introjs-nextbutton:hover {
    background-color: #1d4ed8;
}

.introjs-prevbutton {
    background-color: #e5e7eb;
    color: #1f2937;
}

.introjs-prevbutton:hover {
    background-color: #d1d5db;
}

.introjs-skipbutton {
    color: #6b7280;
    text-decoration: underline;
}

.introjs-skipbutton:hover {
    color: #374151;
}

.introjs-donebutton {
    background-color: #16a34a;
    color: white;
}

.introjs-donebutton:hover {
    background-color: #15803d;
}

.introjs-helperLayer {
    border-radius: 0.5rem;
}

.introjs-overlay {
    background: rgba(0, 0, 0, 0.5);
}

.feature-discovery-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 999998;
    pointer-events: none;
}
</style>