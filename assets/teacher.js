// Базовый путь - работает и в подпапке, и в корне
const BASE_PATH = window.BASE_PATH || '';

let currentLessonId = null;
let timerInterval = null;

function switchTab(tabName) {
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
    
    event.target.classList.add('active');
    document.getElementById(tabName).classList.add('active');
    
    if (tabName === 'history') {
        loadLessons();
    }
}

// Создание занятия
document.getElementById('lesson-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const data = {
        subject_id: document.getElementById('subject').value,
        group_id: document.getElementById('group').value,
        topic: document.getElementById('topic').value
    };
    
    const response = await fetch(BASE_PATH + '/api/teacher/create-lesson', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    });
    
    const result = await response.json();
    
    if (result.success) {
        currentLessonId = result.lesson_id;
        showActiveLesson(result);
    } else {
        alert('Ошибка: ' + result.message);
    }
});

function showActiveLesson(data) {
    document.getElementById('create-form').style.display = 'none';
    document.getElementById('active-lesson').style.display = 'block';
    
    const info = `<strong>Предмет:</strong> ${data.subject} | <strong>Группа:</strong> ${data.group} | <strong>Тема:</strong> ${data.topic}`;
    document.getElementById('lesson-info').innerHTML = info;
    
    // Генерируем QR-код
    const qrContainer = document.getElementById('qr-code');
    qrContainer.innerHTML = '';
    
    const url = window.location.origin + BASE_PATH + '/student?code=' + data.qr_code;
    
    // Проверяем что QRCode загружен
    if (typeof QRCode === 'undefined') {
        qrContainer.innerHTML = '<p style="color: red;">Ошибка загрузки QRCode библиотеки</p>';
        console.error('QRCode library not loaded');
        return;
    }
    
    new QRCode(qrContainer, {
        text: url,
        width: 256,
        height: 256,
        colorDark: "#000000",
        colorLight: "#ffffff",
        correctLevel: QRCode.CorrectLevel.H
    });
    
    // Запускаем таймер
    startTimer(data.created_at);
    
    // Загружаем список студентов
    loadStudents(data.group_id);
}

function startTimer(createdAt) {
    const endTime = new Date(createdAt).getTime() + (15 * 60 * 1000);
    
    timerInterval = setInterval(() => {
        const now = new Date().getTime();
        const remaining = endTime - now;
        
        if (remaining <= 0) {
            clearInterval(timerInterval);
            document.getElementById('timer').textContent = '00:00';
            document.getElementById('timer').style.color = '#dc3545';
            alert('Время для отметки истекло!');
            return;
        }
        
        const minutes = Math.floor(remaining / 60000);
        const seconds = Math.floor((remaining % 60000) / 1000);
        document.getElementById('timer').textContent = 
            `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
    }, 1000);
    
    // Обновляем статусы каждые 3 секунды
    setInterval(() => {
        if (currentLessonId) {
            updateAttendance();
        }
    }, 3000);
}

async function loadStudents(groupId) {
    const response = await fetch(BASE_PATH + `/api/lesson-status?lesson_id=${currentLessonId}`);
    const data = await response.json();
    
    const container = document.getElementById('students-container');
    container.innerHTML = '';
    
    data.students.forEach(student => {
        const div = document.createElement('div');
        div.className = 'student-item' + (student.present ? ' present' : '');
        div.innerHTML = `
            <span>${student.full_name}</span>
            <div>
                <span class="status ${student.present ? 'present' : 'absent'}">
                    ${student.present ? 'Отметился' : 'Не отметился'}
                </span>
                ${!student.present ? 
                    `<button class="mark-btn" onclick="manualMark(${student.id})">Отметить вручную</button>` 
                    : ''}
            </div>
        `;
        container.appendChild(div);
    });
}

async function updateAttendance() {
    const response = await fetch(BASE_PATH + `/api/lesson-status?lesson_id=${currentLessonId}`);
    const data = await response.json();
    
    const container = document.getElementById('students-container');
    container.innerHTML = '';
    
    data.students.forEach(student => {
        const div = document.createElement('div');
        div.className = 'student-item' + (student.present ? ' present' : '');
        div.innerHTML = `
            <span>${student.full_name}</span>
            <div>
                <span class="status ${student.present ? 'present' : 'absent'}">
                    ${student.present ? 'Отметился' : 'Не отметился'}
                </span>
                ${!student.present ? 
                    `<button class="mark-btn" onclick="manualMark(${student.id})">Отметить вручную</button>` 
                    : ''}
            </div>
        `;
        container.appendChild(div);
    });
}

async function manualMark(studentId) {
    const response = await fetch(BASE_PATH + '/api/teacher/manual-mark', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            lesson_id: currentLessonId,
            student_id: studentId
        })
    });
    
    const result = await response.json();
    if (result.success) {
        updateAttendance();
    } else {
        alert('Ошибка: ' + result.message);
    }
}

function endLesson() {
    if (confirm('Завершить занятие?')) {
        clearInterval(timerInterval);
        currentLessonId = null;
        
        document.getElementById('create-form').style.display = 'block';
        document.getElementById('active-lesson').style.display = 'none';
        document.getElementById('lesson-form').reset();
    }
}

// История занятий
async function loadLessons() {
    const response = await fetch(BASE_PATH + '/api/teacher/lessons');
    const lessons = await response.json();
    
    const tbody = document.querySelector('#lessons-table tbody');
    tbody.innerHTML = '';
    
    lessons.forEach(lesson => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${lesson.date}</td>
            <td>${lesson.subject}</td>
            <td>${lesson.group_name}</td>
            <td>${lesson.topic}</td>
            <td>${lesson.present_count}</td>
        `;
        tbody.appendChild(tr);
    });
}

// Экспорт отчета
function exportReport() {
    const form = document.getElementById('export-form');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}

function doExport() {
    const groupId = document.getElementById('export-group').value;
    if (!groupId) {
        alert('Выберите группу');
        return;
    }
    
    window.location.href = `${BASE_PATH}/api/teacher/export?group_id=${groupId}`;
}