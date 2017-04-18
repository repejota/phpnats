from datetime import datetime
import pynats

start = datetime.now()

c = pynats.Connection()
c.connect()

limit = 1000000
for i in range(limit):
    c.publish('foo', None)

c.close()

end = datetime.now()
time_elapsed_secs = (end-start).total_seconds()

speed = limit/time_elapsed_secs
print("Published " + str(limit) + " messages in " + str(time_elapsed_secs) + " seconds")
print(str(int(speed)) + " messages/second")
