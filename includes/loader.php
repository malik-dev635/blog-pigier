<!-- Loader -->
<style>
    .loader-wrapper {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: #fff;
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        transition: opacity 0.5s;
    }

    .student-loader {
        width: 150px;
        height: 150px;
        position: relative;
    }

    .student-loader:before {
        content: "";
        width: 150px;
        height: 150px;
        border-radius: 50%;
        border: 10px solid #020268;
        border-right-color: transparent;
        animation: writing 1s linear infinite;
        position: absolute;
    }

    .student-loader:after {
        content: "✏️";
        font-size: 40px;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        animation: pencil 1s ease-in-out infinite;
    }

    @keyframes writing {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    @keyframes pencil {
        0%, 100% { transform: translate(-50%, -50%) rotate(-15deg); }
        50% { transform: translate(-50%, -50%) rotate(15deg); }
    }

    .loader-hidden {
        opacity: 0;
        pointer-events: none;
    }

    body {
        overflow: hidden;
    }

    body.loaded {
        overflow: auto;
    }
</style>

<div class="loader-wrapper">
    <div class="student-loader"></div>
</div>

<script>
    window.addEventListener('load', () => {
        const loader = document.querySelector('.loader-wrapper');
        document.body.classList.add('loaded');
        
        setTimeout(() => {
            loader.classList.add('loader-hidden');
            loader.addEventListener('transitionend', () => {
                document.body.removeChild(loader);
            });
        }, 500);
    });
</script> 