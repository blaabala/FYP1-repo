<!DOCTYPE html>
<html lang='en'>

<head>
  <meta charset='utf-8' />
  <script>
    import resourceTimelinePlugin from '@fullcalendar/resource-timeline';

    var calendar = new Calendar(calendarEl, {
      schedulerLicenseKey: 'GPL-My-Project-Is-Open-Source',
      plugins: [resourceTimelinePlugin],
      initialView: 'resourceTimelineWeek'
    });

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
        events: [{
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


  <script src='https://cdn.jsdelivr.net/npm/fullcalendar-scheduler@6.1.15/index.global.min.js'></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.30.1/moment.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous"></script>
</body>

</html>