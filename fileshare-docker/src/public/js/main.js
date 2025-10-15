// public/js/main.js

document.addEventListener('DOMContentLoaded', function() {
    // === 공통 기능 ===
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const fileCheckboxes = document.querySelectorAll('.file-checkbox');
    const downloadForm = document.getElementById('downloadForm');
    const fileInput = document.getElementById('shared_file');
    const filenameDisplay = document.querySelector('.selected-filename');

    // '전체 선택' 체크박스
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            fileCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
    }

    // 개별 체크박스
    fileCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            if (!this.checked) {
                selectAllCheckbox.checked = false;
            } else {
                const allChecked = Array.from(fileCheckboxes).every(cb => cb.checked);
                if (allChecked) {
                    selectAllCheckbox.checked = true;
                }
            }
        });
    });
    
    // 파일 선택 시 파일 이름 표시
    if (fileInput && filenameDisplay) {
        fileInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                filenameDisplay.textContent = this.files[0].name;
            } else {
                filenameDisplay.textContent = '선택된 파일 없음';
            }
        });
    }

    // === 로그인 페이지 전용 기능 ===
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');

    if (usernameInput && passwordInput) {
        const passwordGroup = passwordInput.closest('.input-group');
        usernameInput.addEventListener('input', function() {
            if (this.value.toLowerCase() === 'guest') {
                passwordGroup.style.display = 'none';
                passwordInput.required = false;
            } else {
                passwordGroup.style.display = 'block';
                passwordInput.required = true;
            }
        });
    }

    // === 그리드/리스트 뷰 전환 기능 ===
    const gridViewBtn = document.getElementById('gridViewBtn');
    const listViewBtn = document.getElementById('listViewBtn');
    const fileContainer = document.getElementById('fileContainer');

    if (gridViewBtn && listViewBtn && fileContainer) {
        gridViewBtn.addEventListener('click', function() {
            fileContainer.classList.remove('file-list-view');
            fileContainer.classList.add('file-grid');
            gridViewBtn.classList.add('active');
            listViewBtn.classList.remove('active');
            localStorage.setItem('fileView', 'grid');
        });

        listViewBtn.addEventListener('click', function() {
            fileContainer.classList.remove('file-grid');
            fileContainer.classList.add('file-list-view');
            listViewBtn.classList.add('active');
            gridViewBtn.classList.remove('active');
            localStorage.setItem('fileView', 'list');
        });

        const savedView = localStorage.getItem('fileView');
        if (savedView === 'grid') {
            gridViewBtn.click();
        } else {
            listViewBtn.click(); // 기본값은 리스트
        }
    }

    // === 다운로드 진행률 표시 기능 ===
    function showDownloadFeedback(element) {
        // 이미 다운로드 중이면 중복 실행 방지
        if (element.dataset.originalText) return;

        element.dataset.originalText = element.textContent;
        element.textContent = '다운로드 중...';
        element.classList.add('downloading');

        setTimeout(() => {
            element.textContent = element.dataset.originalText;
            element.removeAttribute('data-original-text');
            element.classList.remove('downloading');
        }, 8000); // 8초 후 원래대로 복귀
    }

    // 개별 다운로드 버튼 클릭 시
    document.querySelectorAll('.download-btn').forEach(button => {
        button.addEventListener('click', function() {
            const fileCard = this.closest('.file-card');
            if (fileCard) {
                const sizeElement = fileCard.querySelector('.file-size');
                if (sizeElement) {
                    showDownloadFeedback(sizeElement);
                }
            }
        });
    });

    // 선택 다운로드 폼 제출 시
    if (downloadForm) {
        downloadForm.addEventListener('submit', function(event) {
            const checkedFiles = document.querySelectorAll('.file-checkbox:checked');
            if (checkedFiles.length === 0) {
                alert('다운로드할 파일을 하나 이상 선택해주세요.');
                event.preventDefault();
                return;
            }

            checkedFiles.forEach(checkbox => {
                const fileCard = checkbox.closest('.file-card');
                if (fileCard) {
                    const sizeElement = fileCard.querySelector('.file-size');
                    if (sizeElement) {
                        showDownloadFeedback(sizeElement);
                    }
                }
            });
        });
    }
});

