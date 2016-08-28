$(function(){
    var Task = {

        init: function(){
            this.bindActions();
        },
        bindActions: function(){
            var taskList = $('.task-list');

            $('.task-add').click(this.addTask);

            taskList.on('click', '.task-delete', function(){
                Task.deleteTask($(this).closest('.task-item'));
            });
            taskList.on('change', '.task-status:not(.adding), .task-priority:not(.adding), .task-text:not(.adding)', function(){
                Task.updateTask($(this).closest('.task-item'));
            });
            taskList.on('changeDate', '.calendar:not(.adding)', function(){
                Task.updateTask($(this).closest('.task-item'));
            });
        },
        addTask: function(){
            $.ajax({
                type: 'POST',
                url: 'tasks',
                dataType: 'json',
                data: {
                    text: $('.new-task .task-text').val(),
                    priority: $('.new-task .task-priority').prop('checked'),
                    date: TaskDate.formatDate($('.new-task .calendar').datepicker('getDate'))
                },
                success: function(taskData,status){
                    Task.appendTask($.parseJSON(taskData));
                }
            });
        },
        deleteTask: function(task) {
            $.ajax({
                url: 'tasks',
                type:'DELETE',
                dataType: 'text',
                data: {
                    id: task.attr('id')
                },
                success: function(text, status){
                    task.remove()
                }
            });
        },

        updateTask: function(task){
            $.ajax({
                type: 'PUT',
                url: 'tasks',
                data: {
                    id: task.attr('id'),
                    status: task.find('.task-status').prop('checked'),
                    priority: task.find('.task-priority').prop('checked'),
                    text: task.find('.task-text').val(),
                    date: TaskDate.formatDate(task.find('.calendar').datepicker('getDate'))
                },
                success: function(text, status){
                    //alert('edited');
                }
            });
        },
        appendTask: function(taskData){
            var taskList = $('.task-list').detach();

            var newTask = "<div class='task-item' id='"+taskData.id+"'>"+
            "<div class='input-group'><span class='input-group-addon'>"+
            "<label  class='task-checkbox'>"+
            "<input class='task-status' type='checkbox' title = 'Status' aria-label='Status' "+ (taskData.status === true ? 'checked': '') +">"+
            "<span class='indicator fa fa-check' role='img' aria-label='Important'></span></label></span>"+
            "<input type='text' class='task-text form-control' title='Task' aria-label='Task' value='" + taskData.text + "'>"+
            "<span class='input-group-addon'><label class='task-checkbox'>"+
            "<input class='task-priority' type='checkbox' title = 'Priority' aria-label='Priority' "+ (taskData.priority === true ? 'checked': '') +">"+
            "<span class='indicator fa fa-exclamation' role='img' aria-label='Important'></span></label></span>"+
            "<div class='task-date input-group-btn'><button type='button' class='btn  dropdown-toggle' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'></button>"+
            "<ul class='dropdown-menu dropdown-menu-right'>"+
            "<li><a class='today-btn' href='#'>Today</a></li>"+
            "<li><a class='tomorrow-btn' href='#'>Tomorrow</a></li>"+
            "<li role='separator' class='divider'></li>" +
            "<li><div class='calendar' data-date='" + taskData.date + "'></div><input type='' class='hidden calendar-date'></li></ul></div>"+
            "<div class='input-group-btn'><button class='task-delete btn ' type='button'><span class='fa fa-times' role='img' aria-label='Delete'></span></button></div></div></div>";

            taskList.append(newTask);
            taskList.find('#'+taskData.id).find('.task-status, .task-text, .task-priority, .calendar').addClass('adding');
            taskList.find('#'+taskData.id+' .calendar').datepicker({
                format: TaskDate.format
            }).each(function(){
                TaskDate.setDate($(this))
            });
            taskList.find('#'+taskData.id).find('.task-status, .task-text, .task-priority, .calendar').removeClass('adding');
            $('.tasks').append(taskList);
        }
    };

    var TaskDate = {
        format: 'yyyy-mm-dd',

        setDate: function(calendar){
            var date = calendar.datepicker('getDate').setHours(0,0,0,0);
            var today = new Date().setHours(0,0,0,0);
            var tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate()+1);
            tomorrow = tomorrow.setHours(0,0,0,0);
            if(date == today){
                date = "Today";
            }  else if (date == tomorrow){
                date = "Tommorow";
            } else {
                date = TaskDate.formatDate(calendar.datepicker('getDate'));
            }
            calendar.next('.calendar-date').val(date);
            calendar.parents().prev('.btn').html(date + ' <span class="caret"></span>');
        },

        formatDate: function(date){
            return date.getFullYear() + '-' + ('0' + (date.getMonth()+1)).slice(-2) + '-' + ('0' + date.getDate()).slice(-2);
        },

        setToday: function (calendar) {
            var today = new Date();
            today.setHours(0,0,0,0);
            calendar.datepicker('setDate', today);
        },

        setTomorrow: function (calendar) {
            var tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate()+1);
            tomorrow.setHours(0,0,0,0);
            calendar.datepicker('setDate', tomorrow);
        },

        bindDateActions: function(){
            var taskList = $('.task-list, .new-task');
            taskList.on('changeDate', '.calendar', function(){
                TaskDate.setDate($(this))
            });
            taskList.on('click', '.today-btn', function () {
                TaskDate.setToday($(this).parent().parent().find('.calendar'));
            });
            taskList.on('click', '.tomorrow-btn', function () {
                TaskDate.setTomorrow($(this).parent().parent().find('.calendar'));
            });
        },
        setCalendars: function(){
            $('.calendar').datepicker({
                format: TaskDate.format
            });

            $('.new-task').find('.calendar').each(function(){
                TaskDate.setToday($(this));
            });

            $('.task-list').find('.calendar').each(function(){
                TaskDate.setDate($(this));
            });
        },
        init: function(){
            this.setCalendars();
            this.bindDateActions();
        }
    };

    TaskDate.init();
    Task.init();
});