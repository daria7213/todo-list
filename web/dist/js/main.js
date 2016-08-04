
$(function(){

    var task = {

        init: function(){
            taskDate.init();
            $('.task-add').click(task.addTask);
            $('.task-list').on('click', '.task-delete', function(){
                task.deleteTask($(this).closest('.task-item'));
            });
            $('.task-list').on('change', '.task-status:not(.adding), .task-priority:not(.adding), .task-text:not(.adding)', function(){
                task.updateTask($(this).closest('.task-item'));
            });
            $('.task-list').on('changeDate', '.calendar:not(.adding)', function(){
                task.updateTask($(this).closest('.task-item'));
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
                    date: taskDate.formatDate($('.new-task .calendar').datepicker('getDate'))
                },
                success: function(taskData,status){
                    task.appendTask($.parseJSON(taskData));
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
                    date: taskDate.formatDate(task.find('.calendar').datepicker('getDate'))
                },
                success: function(text, status){
                    alert('edited');
                }
            });
        },
        appendTask: function(taskData){
            var taskList = $('.task-list').detach();

            var newTask = "<div class='task-item' id='"+taskData.id+"'>"+
            "<div class='input-group'><span class='input-group-addon'>"+
            "<input class='task-status' type='checkbox' title = 'Status' aria-label='Status' "+ (taskData.status === true ? 'checked': '') +"></span>"+
            "<input type='text' class='task-text form-control' title='Task' aria-label='Task' value='" + taskData.text + "'>"+
            "<span class='input-group-addon'><label>"+
            "<input class='task-priority' type='checkbox' title = 'Important' aria-label='Status' "+ (taskData.priority === true ? 'checked': '') +">Important!</label></span>"+
            "<div class='task-date input-group-btn'><button type='button' class='btn btn-default dropdown-toggle' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'></button>"+
            "<ul class='dropdown-menu dropdown-menu-right'>"+
            "<li><a class='today-btn' href='#'>Today</a></li>"+
            "<li><a class='tomorrow-btn' href='#'>Tomorrow</a></li>"+
            "<li role='separator' class='divider'></li>" +
            "<li><div class='calendar' data-date='" + taskData.date + "'></div><input type='' class='hidden calendar-date'></li></ul></div>"+
            "<div class='input-group-btn'><button class='task-delete btn btn-default' type='button'>X</button></div></div></div>";

            taskList.append(newTask);
            taskList.find('#'+taskData.id).find('.task-status, .task-text, .task-priority, .calendar').addClass('adding');
            taskList.find('#'+taskData.id+' .calendar').datepicker({
                format: taskDate.format
            }).each(function(){
                taskDate.setDate($(this))
            });
            taskList.find('#'+taskData.id).find('.task-status, .task-text, .task-priority, .calendar').removeClass('adding');
            $('.task-panel .panel-body').append(taskList);
        }
    };

    var taskDate = {
        format: 'yyyy-mm-dd',

        setDate: function(calendar){
            var date = calendar.datepicker('getDate');
            if(date.setHours(0,0,0,0) == (new Date).setHours(0,0,0,0)){
                date = "Today";
            } else {
                date = taskDate.formatDate(calendar.datepicker('getDate'));
            }
            calendar.next('.calendar-date').val(date);
            calendar.parents().prev('.btn').html(date + ' <span class="caret"></span>');
        },

        formatDate: function(date){
            return date.getFullYear() + '-' + ('0' + (date.getMonth()+1)).slice(-2) + '-' + ('0' + date.getDate()).slice(-2);
        },
        foo: function(){
            var t = this;
            var tt = $(this);
            $.proxy(taskDate.setToday(), tt);
        },

        setToday: function (calendar) {
            var today = new Date();
            today.setHours(0,0,0,0);
            calendar.datepicker('setDate', today);
        },

        init: function(){
            $('.calendar').datepicker({
                format: taskDate.format
            });

            $('.new-task').find('.calendar').each(function(){
                taskDate.setToday($(this));
            });

            $('.task-list').find('.calendar').each(function(){
                taskDate.setDate($(this));
            });
            $('.task-list, .new-task').on('changeDate', '.calendar', function(){
                taskDate.setDate($(this))
            });
            $('.task-list, .new-task').on('click', '.today-btn', function () {
                taskDate.setToday($(this).parent().parent().find('.calendar'));
            });
        }
    };

    task.init();


    //$('.calendar').datepicker({
    //    format: 'dd M dd'
    //});
    //$('.calendar').on("changeDate", function() {
    //    var date = $(this).datepicker('getFormattedDate');
    //    $(this).next('.calendar-date').val(date);
    //    $(this).parents().prev('.btn').html(date + ' <span class="caret"></span>');
    //
    //});
    //
    //$('.date').find('button').each(function(){
    //    var date = $(this).next().find('.calendar').datepicker('getFormattedDate');
    //    $(this).html(date + '<span class="caret"></span>');
    //});

});