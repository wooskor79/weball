// File: app/assets/js/main.js

document.addEventListener('DOMContentLoaded', () => {
    // 다크 모드 토글 스위치
    const themeSwitch = document.getElementById('theme-switch');
    if (themeSwitch) {
        if (localStorage.getItem('theme') === 'dark') {
            themeSwitch.checked = true;
        }
        themeSwitch.addEventListener('change', function(event) {
            if (event.currentTarget.checked) {
                document.documentElement.classList.add('dark-mode');
                localStorage.setItem('theme', 'dark');
            } else {
                document.documentElement.classList.remove('dark-mode');
                localStorage.setItem('theme', 'light');
            }
        });
    }
});

// 그룹 펼치기/닫기
function openGroup(content, icon) {
    content.style.paddingTop = '1rem';
    content.style.paddingBottom = '1rem';
    content.style.maxHeight = content.scrollHeight + 20 + "px";
    icon.style.transform = 'rotate(180deg)';
    setTimeout(() => content.style.maxHeight = 'none', 300);
}

function closeGroup(content, icon) {
    content.style.maxHeight = content.scrollHeight + "px";
    content.offsetHeight;
    content.style.maxHeight = '0px';
    content.style.paddingTop = '0';
    content.style.paddingBottom = '0';
    icon.style.transform = 'rotate(0deg)';
}

function toggleGroup(headerElement) {
    const content = headerElement.nextElementSibling;
    const icon = headerElement.querySelector('svg');
    if (content.style.maxHeight && content.style.maxHeight !== '0px' && content.style.maxHeight !== 'none') {
        closeGroup(content, icon);
    } else {
        openGroup(content, icon);
    }
}
