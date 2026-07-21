<?php if (empty($is_owner)) : ?>
    <span class="badge badge-readonly">
        <svg class="badge-icon" viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
            <rect x="4" y="10" width="16" height="10" rx="2"></rect>
            <path d="M7 10V7a5 5 0 0 1 10 0v3"></path>
        </svg>
        Lecture seule
    </span>
<?php endif; ?>