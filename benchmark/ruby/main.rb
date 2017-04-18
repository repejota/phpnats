require "nats/client"

start = Time.now()

limit = 100000

NATS.start do
  for i in 0..limit
    NATS.publish('foo', nil) {}
  end

  NATS.stop
end

eend = Time.now()

time_elapsed_secs = eend - start

speed = limit/time_elapsed_secs

print "Published " + limit.to_s + " messages in " + time_elapsed_secs.to_s + " seconds\n"
print speed.round.to_s + " messages/second\n"
