package main

import (
	"fmt"
	"strconv"
	"time"

	"github.com/nats-io/nats"
)

func main() {
	start := time.Now()

	nc, _ := nats.Connect(nats.DefaultURL)
	limit := 100000
	for i := 0; i < limit; i++ {
		nc.Publish("foo", nil)
	}
	nc.Close()

	time_elapsed_secs := time.Since(start).Seconds()

	speed := float64(limit) / time_elapsed_secs
	fmt.Println("Published " + strconv.Itoa(limit) + " messages in " + strconv.FormatFloat(time_elapsed_secs, 'f', 20, 64) + " seconds")
	fmt.Println(strconv.FormatFloat(speed, 'f', 0, 64) + " messages/second")
}
