function initBookingCalendar() {
  new Calendar("booking-calendar", {
    url: WEB_URL + "index.php/booking/model/calendar/toJSON",
    onclick: function(d) {
      send(
        WEB_URL + "index.php/booking/model/index/action",
        "action=detail&id=" + this.id,
        doFormSubmit
      );
    }
  });
  forEach($E('room_links').getElementsByTagName('a'), function() {
    callClick(this, function() {
      send(
        WEB_URL + "index.php/booking/model/rooms/action",
        'action=detail&id=' + this.id.replace('room_', ''),
        doFormSubmit,
        this
      );
    });
  });
}


function initBooking() {
  $G('begin_date').addEvent("change", function() {
    if (this.value != "") {
      $E('end_date').min = this.value;
    }
  });
}

function initBookingOrder() {
  $G('begin_date').addEvent("change", function() {
    if (this.value != "") {
      $E('end_date').min = this.value;
    }
  });
  if ($E('send_mail')) {
    var status = $E('status').value;
    $G('status').addEvent('change', function() {
      $E('send_mail').checked = status != this.value;
    });
  }
}