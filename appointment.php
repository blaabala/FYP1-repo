<!DOCTYPE html>
<html lang='en'>
  <head>
    <meta charset='utf-8' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar-scheduler@6.1.15/index.global.min.js'></script>
    <script>

    //   document.addEventListener('DOMContentLoaded', function() {
    //     var calendarEl = document.getElementById('calendar');
    //     var calendar = new FullCalendar.Calendar(calendarEl, {
    //       initialView: 'resourceTimelineWeek'
    //     });
    //     calendar.render();
    //   });
    document.addEventListener('DOMContentLoaded', function() {
  var calendarEl = document.getElementById('calendar');

  var calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    initialDate: '2024-07-07',
    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: 'dayGridMonth,timeGridWeek,timeGridDay'
    },
    events: [
      {
        title: 'All Day Event',
        start: '2024-07-01'
      },
      {
        title: 'Long Event',
        start: '2024-07-07',
        end: '2024-07-10'
      },
      {
        groupId: '999',
        title: 'Repeating Event',
        start: '2024-07-09T16:00:00'
      },
      {
        groupId: '999',
        title: 'Repeating Event',
        start: '2024-07-16T16:00:00'
      },
      {
        title: 'Conference',
        start: '2024-07-11',
        end: '2024-07-13'
      },
      {
        title: 'Meeting',
        start: '2024-07-12T10:30:00',
        end: '2024-07-12T12:30:00'
      },
      {
        title: 'Lunch',
        start: '2024-07-12T12:00:00'
      },
      {
        title: 'Meeting',
        start: '2024-07-12T14:30:00'
      },
      {
        title: 'Birthday Party',
        start: '2024-07-13T07:00:00'
      },
      {
        title: 'Click for Google',
        url: 'https://google.com/',
        start: '2024-07-28'
      }
    ]
  });

  calendar.render();
});

    </script>
  </head>
  <body>

    <div id='calendar'></div>
  </body>
</html>