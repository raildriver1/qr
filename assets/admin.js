// Переключение табов
function switchTab(tabName) {
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
    
    event.target.classList.add('active');
    document.getElementById(tabName).classList.add('active');
    
    if (tabName === 'teachers') loadTeachers();
    if (tabName === 'groups') loadGroups();
    if (tabName === 'subjects') loadSubjects();
    if (tabName === 'students') loadStudents();
}

// Показать сообщение
function showMessage(containerId, message, type = 'success') {
    const container = document.getElementById(containerId);
    container.innerHTML = `<div class="${type}">${message}</div>`;
    setTimeout(() => container.innerHTML = '', 3000);
}

// ==================== ПРЕПОДАВАТЕЛИ ====================
async function loadTeachers() {
    const response = await fetch('/api/admin/teachers');
    const data = await response.json();
    
    const tbody = document.querySelector('#teachers-table tbody');
    tbody.innerHTML = '';
    
    data.forEach(teacher => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${teacher.full_name}</td>
            <td>${teacher.login}</td>
            <td>${teacher.password}</td>
            <td>${teacher.subjects || 'Нет'}</td>
            <td>
                <button class="action-btn edit-btn" onclick="editTeacher(${teacher.id})">Изменить</button>
                <button class="action-btn delete-btn" onclick="deleteTeacher(${teacher.id})">Удалить</button>
            </td>
        `;
        tbody.appendChild(tr);
    });
    
    await loadSubjectsForTeacher();
}

async function loadSubjectsForTeacher() {
    const response = await fetch('/api/admin/subjects');
    const subjects = await response.json();
    
    const container = document.getElementById('teacher-subjects');
    container.innerHTML = '';
    
    subjects.forEach(subject => {
        const div = document.createElement('div');
        div.className = 'checkbox-item';
        div.innerHTML = `
            <label>
                <input type="checkbox" name="subject" value="${subject.id}">
                ${subject.name}
            </label>
        `;
        container.appendChild(div);
    });
}

document.getElementById('teacher-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const id = document.getElementById('teacher-id').value;
    const data = {
        full_name: document.getElementById('teacher-name').value,
        login: document.getElementById('teacher-login').value,
        password: document.getElementById('teacher-password').value,
        subjects: Array.from(document.querySelectorAll('input[name="subject"]:checked')).map(cb => cb.value)
    };
    
    const method = id ? 'PUT' : 'POST';
    const url = id ? `/api/admin/teachers?id=${id}` : '/api/admin/teachers';
    
    const response = await fetch(url, {
        method,
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    });
    
    const result = await response.json();
    showMessage('teachers-message', result.message, result.success ? 'success' : 'error');
    
    if (result.success) {
        resetTeacherForm();
        loadTeachers();
    }
});

function editTeacher(id) {
    fetch(`/api/admin/teachers?id=${id}`)
        .then(r => r.json())
        .then(teacher => {
            document.getElementById('teacher-id').value = teacher.id;
            document.getElementById('teacher-name').value = teacher.full_name;
            document.getElementById('teacher-login').value = teacher.login;
            document.getElementById('teacher-password').value = teacher.password;
            
            document.querySelectorAll('input[name="subject"]').forEach(cb => {
                cb.checked = teacher.subject_ids.includes(parseInt(cb.value));
            });
        });
}

async function deleteTeacher(id) {
    if (!confirm('Удалить преподавателя?')) return;
    
    const response = await fetch(`/api/admin/teachers?id=${id}`, {method: 'DELETE'});
    const result = await response.json();
    showMessage('teachers-message', result.message, result.success ? 'success' : 'error');
    
    if (result.success) loadTeachers();
}

function resetTeacherForm() {
    document.getElementById('teacher-form').reset();
    document.getElementById('teacher-id').value = '';
    document.querySelectorAll('input[name="subject"]').forEach(cb => cb.checked = false);
}

// ==================== ГРУППЫ ====================
async function loadGroups() {
    const response = await fetch('/api/admin/groups');
    const data = await response.json();
    
    const tbody = document.querySelector('#groups-table tbody');
    tbody.innerHTML = '';
    
    data.forEach(group => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${group.name}</td>
            <td>
                <button class="action-btn edit-btn" onclick="editGroup(${group.id})">Изменить</button>
                <button class="action-btn delete-btn" onclick="deleteGroup(${group.id})">Удалить</button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

document.getElementById('group-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const id = document.getElementById('group-id').value;
    const data = {name: document.getElementById('group-name').value};
    
    const method = id ? 'PUT' : 'POST';
    const url = id ? `/api/admin/groups?id=${id}` : '/api/admin/groups';
    
    const response = await fetch(url, {
        method,
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    });
    
    const result = await response.json();
    showMessage('groups-message', result.message, result.success ? 'success' : 'error');
    
    if (result.success) {
        resetGroupForm();
        loadGroups();
    }
});

function editGroup(id) {
    fetch(`/api/admin/groups?id=${id}`)
        .then(r => r.json())
        .then(group => {
            document.getElementById('group-id').value = group.id;
            document.getElementById('group-name').value = group.name;
        });
}

async function deleteGroup(id) {
    if (!confirm('Удалить группу?')) return;
    
    const response = await fetch(`/api/admin/groups?id=${id}`, {method: 'DELETE'});
    const result = await response.json();
    showMessage('groups-message', result.message, result.success ? 'success' : 'error');
    
    if (result.success) loadGroups();
}

function resetGroupForm() {
    document.getElementById('group-form').reset();
    document.getElementById('group-id').value = '';
}

// ==================== ПРЕДМЕТЫ ====================
async function loadSubjects() {
    const response = await fetch('/api/admin/subjects');
    const data = await response.json();
    
    const tbody = document.querySelector('#subjects-table tbody');
    tbody.innerHTML = '';
    
    data.forEach(subject => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${subject.name}</td>
            <td>
                <button class="action-btn edit-btn" onclick="editSubject(${subject.id})">Изменить</button>
                <button class="action-btn delete-btn" onclick="deleteSubject(${subject.id})">Удалить</button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

document.getElementById('subject-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const id = document.getElementById('subject-id').value;
    const data = {name: document.getElementById('subject-name').value};
    
    const method = id ? 'PUT' : 'POST';
    const url = id ? `/api/admin/subjects?id=${id}` : '/api/admin/subjects';
    
    const response = await fetch(url, {
        method,
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    });
    
    const result = await response.json();
    showMessage('subjects-message', result.message, result.success ? 'success' : 'error');
    
    if (result.success) {
        resetSubjectForm();
        loadSubjects();
    }
});

function editSubject(id) {
    fetch(`/api/admin/subjects?id=${id}`)
        .then(r => r.json())
        .then(subject => {
            document.getElementById('subject-id').value = subject.id;
            document.getElementById('subject-name').value = subject.name;
        });
}

async function deleteSubject(id) {
    if (!confirm('Удалить предмет?')) return;
    
    const response = await fetch(`/api/admin/subjects?id=${id}`, {method: 'DELETE'});
    const result = await response.json();
    showMessage('subjects-message', result.message, result.success ? 'success' : 'error');
    
    if (result.success) loadSubjects();
}

function resetSubjectForm() {
    document.getElementById('subject-form').reset();
    document.getElementById('subject-id').value = '';
}

// ==================== СТУДЕНТЫ ====================
async function loadStudents() {
    const response = await fetch('/api/admin/students');
    const data = await response.json();
    
    const tbody = document.querySelector('#students-table tbody');
    tbody.innerHTML = '';
    
    data.forEach(student => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${student.full_name}</td>
            <td>${student.group_name}</td>
            <td>
                <button class="action-btn edit-btn" onclick="editStudent(${student.id})">Изменить</button>
                <button class="action-btn delete-btn" onclick="deleteStudent(${student.id})">Удалить</button>
            </td>
        `;
        tbody.appendChild(tr);
    });
    
    await loadGroupsForStudent();
}

async function loadGroupsForStudent() {
    const response = await fetch('/api/admin/groups');
    const groups = await response.json();
    
    const select = document.getElementById('student-group');
    select.innerHTML = '<option value="">Выберите группу</option>';
    
    groups.forEach(group => {
        const option = document.createElement('option');
        option.value = group.id;
        option.textContent = group.name;
        select.appendChild(option);
    });
}

document.getElementById('student-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const id = document.getElementById('student-id').value;
    const data = {
        full_name: document.getElementById('student-name').value,
        group_id: document.getElementById('student-group').value
    };
    
    const method = id ? 'PUT' : 'POST';
    const url = id ? `/api/admin/students?id=${id}` : '/api/admin/students';
    
    const response = await fetch(url, {
        method,
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    });
    
    const result = await response.json();
    showMessage('students-message', result.message, result.success ? 'success' : 'error');
    
    if (result.success) {
        resetStudentForm();
        loadStudents();
    }
});

function editStudent(id) {
    fetch(`/api/admin/students?id=${id}`)
        .then(r => r.json())
        .then(student => {
            document.getElementById('student-id').value = student.id;
            document.getElementById('student-name').value = student.full_name;
            document.getElementById('student-group').value = student.group_id;
        });
}

async function deleteStudent(id) {
    if (!confirm('Удалить студента?')) return;
    
    const response = await fetch(`/api/admin/students?id=${id}`, {method: 'DELETE'});
    const result = await response.json();
    showMessage('students-message', result.message, result.success ? 'success' : 'error');
    
    if (result.success) loadStudents();
}

function resetStudentForm() {
    document.getElementById('student-form').reset();
    document.getElementById('student-id').value = '';
}

// Загружаем данные при запуске
loadTeachers();