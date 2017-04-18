import pynats

if __name__ == '__main__':
    c = pynats.Connection()
    c.connect()

    limit = 1000000
    for i in range(limit):
        print(i)
        c.publish('foo', None)

    c.close()
