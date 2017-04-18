var NATS = require('nats');

var start = new Date();

var nats = NATS.connect();

var limit = 100000;

for (var i=0;i<limit;i++) {
  nats.publish('foo', null, function() {
    if (i >= limit) {
      nats.close();

      var end = new Date();
      var time_elapsed_secs = (end-start)/1000;

      var speed = limit/time_elapsed_secs;

      console.log("Published " + limit + " messages in " + time_elapsed_secs + " seconds");
      console.log(parseInt(speed) + " messages/second");
    }
  });
}


