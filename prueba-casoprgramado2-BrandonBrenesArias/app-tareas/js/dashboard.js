document.addEventListener('DOMContentLoaded', function () {

    let isEditMode = false;
    let edittingId;
    let tasks = [];
    const API_URL = 'backend/tasks.php';

    async function loadTasks() {
        try {
            const response = await fetch('./backend/tasks.php', {
                method: 'GET',
                credentials: 'include'
            });
    
            if (!response.ok) {
                throw new Error('Error al cargar tareas');
            }
    
            const tasks = await response.json();
    
            // Decodificar el campo comments
            tasks.forEach(task => {
                if (typeof task.comments === 'string') {
                    task.comments = JSON.parse(task.comments || '[]');
                }
            });
    
            console.log(tasks); // Verifica los datos decodificados
            renderTasks(tasks);
        } catch (error) {
            console.error(error);
            alert('Error cargando las tareas');
        }
    }
    

    function renderTasks(tasks) {
        //traer las tareas desde el backend
        const taskList = document.getElementById('task-list');
        taskList.innerHTML = '';
        tasks.forEach(function (task) {

            let commentsList = '';
            if (task.comments && task.comments.length > 0) {
                commentsList = '<ul class="list-group list-group-flush">';
                task.comments.forEach(comment => {
                    commentsList += `
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>${comment.description}</span>
                        <div>
                            <button class="btn btn-sm btn-primary edit-comment" data-commentid="${comment.id}" data-taskid="${task.id}">Edit</button>
                            <button class="btn btn-sm btn-danger remove-comment" data-commentid="${comment.id}" data-taskid="${task.id}">Delete</button>
                        </div>
                    </li>`;
                });
                commentsList += '</ul>';
            } else {
                commentsList = '<p class="text-muted">No comments yet.</p>';
            }

            const taskCard = document.createElement('div');
            taskCard.className = 'col-md-4 mb-3';
            taskCard.innerHTML = `
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">${task.title}</h5>
                    <p class="card-text">${task.description}</p>
                    <p class="card-text"><small class="text-muted">Due: ${task.due_date}</small> </p>
                    ${commentsList}
                     <button type="button" class="btn btn-sm btn-link add-comment"  data-id="${task.id}">Add Comment</button>

                </div>
                <div class="card-footer d-flex justify-content-between">
                    <button class="btn btn-secondary btn-sm edit-task"data-id="${task.id}">Edit</button>
                    <button class="btn btn-danger btn-sm delete-task" data-id="${task.id}">Delete</button>
                </div>
            </div>
            `;
            taskList.appendChild(taskCard);
        });

        document.querySelectorAll('.edit-task').forEach(function (button) {
            button.addEventListener('click', handleEditTask);
        });

        document.querySelectorAll('.delete-task').forEach(function (button) {
            button.addEventListener('click', handleDeleteTask);
        });

        document.querySelectorAll('.add-comment').forEach(function (button) {
            button.addEventListener('click', function (e) {
                document.getElementById("comment-task-id").value = e.target.dataset.id;
                const modal = new bootstrap.Modal(document.getElementById("commentModal"));
                modal.show()
            });
        });

        document.querySelectorAll('.remove-comment').forEach(function (button) {
            button.addEventListener('click', async function (e) {
                const commentId = parseInt(e.target.dataset.commentid);
                const taskId = parseInt(e.target.dataset.taskid);
                try {
                    const response = await fetch(`${API_URL}?comment_id=${commentId}`, {
                        method: 'DELETE',
                        credentials: 'include'
                    });

                    if (!response.ok) throw new Error('Error deleting comment');

                    alert('Comment deleted successfully!');
                    loadTasks(); // Recargar tareas y comentarios
                } catch (error) {
                    console.error(error.message);
                    alert('Failed to delete comment.');
                }
            });
        });

        document.querySelectorAll('.edit-comment').forEach(function (button) {
            button.addEventListener('click', function (e) {
                const commentId = parseInt(e.target.dataset.commentid);
                const taskId = parseInt(e.target.dataset.taskid);
                const commentText = e.target.closest('li').querySelector('span').textContent;

                document.getElementById('task-comment').value = commentText;
                document.getElementById('comment-task-id').value = taskId;
                document.getElementById('delete-comment-btn').dataset.commentid = commentId;
                const modal = new bootstrap.Modal(document.getElementById('commentModal'));
                modal.show();

                // Cambiar el comportamiento del formulario para editar
                document.getElementById('comment-form').onsubmit = async function (e) {
                    e.preventDefault();
                    const updatedComment = document.getElementById('task-comment').value;
                    try {
                        const response = await fetch(`${API_URL}?comment_id=${commentId}`, {
                            method: 'PUT',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ comment: updatedComment }),
                            credentials: 'include'
                        });
                        if (!response.ok) throw new Error('Error updating comment');
                        alert('Comment updated successfully!');
                        bootstrap.Modal.getInstance(document.getElementById('commentModal')).hide();
                        loadTasks();
                    } catch (error) {
                        console.error(error.message);
                        alert('Failed to update comment.');
                    }
                };
            });
        });
    }


    function handleEditTask(event) {
        try {
            const taskId = parseInt(event.target.dataset.id);
            const task = tasks.find(t => t.id === taskId);
            //cargar los datos en el formulario 
            document.getElementById('task-title').value = task.title;
            document.getElementById('task-desc').value = task.description;
            document.getElementById('due-date').value = task.due_date;
            //ponerlo en modo edicion
            isEditMode = true;
            edittingId = taskId;
            //mostrar el modal
            const modal = new bootstrap.Modal(document.getElementById("taskModal"));
            modal.show();

        } catch (error) {
            alert("Error trying to edit a task");
            console.error(error);
        }
    }

    async function handleDeleteTask(event) {
        const id = parseInt(event.target.dataset.id);
        const response = await fetch(`${API_URL}?id=${id}`,{
            method: 'DELETE',
            credentials: 'include'
        });
        if (response.ok) {
            loadTasks();
        } else {
            console.error("Error eliminando las tareas");
        }
    }

    document.getElementById('comment-form').addEventListener('submit', async function (e) {
        e.preventDefault();

        const comment = document.getElementById('task-comment').value;
        const taskId = parseInt(document.getElementById('comment-task-id').value);

        try {
            const response = await fetch(`${API_URL}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ task_id: taskId, comment: comment }),
                credentials: 'include'
            });

            if (!response.ok) throw new Error('Error adding comment');

            alert('Comment added successfully!');
            const modal = bootstrap.Modal.getInstance(document.getElementById('commentModal'));
            modal.hide();
            loadTasks();
        } catch (error) {
            console.error(error.message);
            alert('Failed to add comment.');
        }
    });

    document.getElementById('task-form').addEventListener('submit', async function (e) {
        e.preventDefault();

        const title = document.getElementById("task-title").value;
        const description = document.getElementById("task-desc").value;
        const dueDate = document.getElementById("due-date").value;

        if (isEditMode) {
            const response = await fetch(`${API_URL}?id=${edittingId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ title: title, description: description, due_date: dueDate }),
                credentials: "include"
            });
            if (!response.ok) {
                console.error("Sucedio un error");
            }

        } else {
            const newTask = {
                title: title,
                description: description,
                due_date: dueDate
            };
            const response = await fetch(API_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(newTask),
                credentials: "include"
            });
            if (!response.ok) {
                console.error("Sucedio un error");
            }
        }
        const modal = bootstrap.Modal.getInstance(document.getElementById('taskModal'));
        modal.hide();
        loadTasks();
    });

    document.getElementById('commentModal').addEventListener('show.bs.modal', function () {
        document.getElementById('comment-form').reset();
    });

    document.getElementById('taskModal').addEventListener('show.bs.modal', function () {
        if (!isEditMode) {
            document.getElementById('task-form').reset();
        }
    });

    document.getElementById("taskModal").addEventListener('hidden.bs.modal', function () {
        edittingId = null;
        isEditMode = false;
    });

    loadTasks();

});
