// File: app/assets/js/admin.js

// HTML 특수문자 이스케이프 함수
function escapeHTML(str) {
    if (typeof str !== 'string') return '';
    return str.replace(/[&<>'"]/g, 
        tag => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' }[tag] || tag)
    );
}

document.addEventListener('DOMContentLoaded', () => {
    // 다크 모드 토글
    const themeSwitch = document.getElementById('theme-switch');
    if (themeSwitch) {
        if (localStorage.getItem('theme') === 'dark') themeSwitch.checked = true;
        themeSwitch.addEventListener('change', function(event) {
            document.documentElement.classList.toggle('dark-mode', event.currentTarget.checked);
            localStorage.setItem('theme', event.currentTarget.checked ? 'dark' : 'light');
        });
    }

    // 메모 추가 폼 (AJAX)
    const memoForm = document.getElementById("memoForm");
    if (memoForm) {
        memoForm.addEventListener("submit", async (e) => {
            e.preventDefault();
            const titleInput = document.getElementById("memo-title");
            const contentInput = document.getElementById("memo-content");
            if (!titleInput.value.trim() || !contentInput.value.trim()) return alert("제목과 내용을 입력하세요.");

            const formData = new FormData();
            formData.append('title', titleInput.value.trim());
            formData.append('content', contentInput.value.trim());

            try {
                const res = await fetch("actions/add_memo.php", {
                    method: "POST",
                    body: new URLSearchParams(formData)
                });
                if (res.ok) window.location.reload();
                else alert("메모 추가 실패: " + await res.text());
            } catch (error) {
                console.error('Error:', error);
                alert("오류가 발생했습니다.");
            }
        });
    }
});

// 인라인 삭제 확인 (공용)
function showAdminConfirm(buttonElement) {
    const container = buttonElement.closest('.admin-default-view');
    if (container) {
        const confirmView = container.nextElementSibling;
        container.classList.add('hidden');
        confirmView.classList.remove('hidden');
        confirmView.classList.add('flex');
    }
}

function hideAdminConfirm(buttonElement) {
    const container = buttonElement.closest('.admin-confirm-view');
    if (container) {
        const defaultView = container.previousElementSibling;
        container.classList.add('hidden');
        container.classList.remove('flex');
        defaultView.classList.remove('hidden');
    }
}


// 즐겨찾기 수정 기능
function showEditFavorite(id) {
    const row = document.getElementById(`fav-row-${id}`);
    row.querySelectorAll('.view-mode').forEach(el => el.classList.add('hidden'));
    row.querySelectorAll('.edit-mode').forEach(el => el.classList.remove('hidden'));
}

function hideEditFavorite(id) {
    const row = document.getElementById(`fav-row-${id}`);
    row.querySelectorAll('.view-mode').forEach(el => el.classList.remove('hidden'));
    row.querySelectorAll('.edit-mode').forEach(el => el.classList.add('hidden'));
}

async function saveFavorite(id) {
    const row = document.getElementById(`fav-row-${id}`);
    const aliasInput = row.querySelector('td:nth-child(1) .edit-mode');
    const urlInput = row.querySelector('td:nth-child(2) .edit-mode');

    const formData = new FormData();
    formData.append('id', id);
    formData.append('alias', aliasInput.value.trim());
    formData.append('url', urlInput.value.trim());

    try {
        const res = await fetch('actions/edit_favorite.php', { method: 'POST', body: new URLSearchParams(formData) });
        const data = await res.json();
        if (res.ok) {
            row.querySelector('td:nth-child(1) .view-mode').textContent = data.alias;
            const link = row.querySelector('td:nth-child(2) .view-mode a');
            link.href = data.url;
            link.textContent = data.url;
            hideEditFavorite(id);
        } else {
            alert('즐겨찾기 저장 실패: ' + (data.error || '알 수 없는 오류'));
        }
    } catch (error) {
        console.error('Save favorite error:', error);
        alert('저장 중 오류가 발생했습니다.');
    }
}

// 빠른 링크 수정 기능
function showEditLink(id) {
    const row = document.getElementById(`link-row-${id}`);
    row.querySelectorAll('.view-mode').forEach(el => el.classList.add('hidden'));
    row.querySelectorAll('.edit-mode').forEach(el => el.classList.remove('hidden'));
}

function hideEditLink(id) {
    const row = document.getElementById(`link-row-${id}`);
    row.querySelectorAll('.view-mode').forEach(el => el.classList.remove('hidden'));
    row.querySelectorAll('.edit-mode').forEach(el => el.classList.add('hidden'));
}

async function saveLink(id) {
    const row = document.getElementById(`link-row-${id}`);
    const titleInput = row.querySelector('td:nth-child(1) .edit-mode');
    const urlInput = row.querySelector('td:nth-child(2) .edit-mode');

    const formData = new FormData();
    formData.append('id', id);
    formData.append('title', titleInput.value.trim());
    formData.append('url', urlInput.value.trim());

    try {
        const res = await fetch('actions/edit_link.php', { method: 'POST', body: new URLSearchParams(formData) });
        const data = await res.json();
        if (res.ok) {
            row.querySelector('td:nth-child(1) .view-mode').textContent = data.title;
            const link = row.querySelector('td:nth-child(2) .view-mode a');
            link.href = data.url;
            link.textContent = data.url;
            hideEditLink(id);
        } else {
            alert('빠른 링크 저장 실패: ' + (data.error || '알 수 없는 오류'));
        }
    } catch (error) {
        console.error('Save link error:', error);
        alert('저장 중 오류가 발생했습니다.');
    }
}


// 메모 수정/삭제 기능
function showEditMemo(id) {
    const memoCard = document.getElementById(`memo-${id}`);
    if (memoCard) {
        memoCard.querySelector('.memo-display-view').classList.add('hidden');
        memoCard.querySelector('.memo-edit-view').classList.remove('hidden');
    }
}

function hideEditMemo(id) {
    const memoCard = document.getElementById(`memo-${id}`);
    if (memoCard) {
        memoCard.querySelector('.memo-display-view').classList.remove('hidden');
        memoCard.querySelector('.memo-edit-view').classList.add('hidden');
    }
}

async function saveMemo(id) {
    const memoCard = document.getElementById(`memo-${id}`);
    const title = memoCard.querySelector('.memo-edit-title').value.trim();
    if (!title) return alert('제목을 입력해주세요.');
    const formData = new FormData();
    formData.append('id', id);
    formData.append('title', title);
    formData.append('content', memoCard.querySelector('.memo-edit-content').value.trim());

    try {
        const res = await fetch('actions/edit_memo.php', { method: 'POST', body: new URLSearchParams(formData) });
        const data = await res.json();
        if (res.ok) {
            memoCard.querySelector('.memo-title-text').textContent = data.title;
            memoCard.querySelector('.memo-content-text').textContent = data.content; // 수정된 부분
            hideEditMemo(id);
        } else {
            alert('메모 저장 실패: ' + (data.error || '알 수 없는 오류'));
        }
    } catch (error) { alert('저장 중 오류가 발생했습니다.'); }
}

async function deleteMemo(id) {
    try {
        const res = await fetch(`actions/delete_memo.php?id=${id}`);
        if (res.ok) {
            document.getElementById(`memo-${id}`)?.remove();
        } else {
            alert('메모 삭제 실패: ' + await res.text());
        }
    } catch (error) { alert('오류가 발생했습니다.'); }
}