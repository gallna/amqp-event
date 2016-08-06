# Dispatcher
Parallel Event-Based Gateway

As the name suggest, this gateway is similar to a parallel gateway. It allows for multiple processes to happen at the same time, but unlike the parallel gateway, the processes are event dependent. You can think of a parallel event-based gateway as a non-exclusive, event-based gateway where multiple events can trigger multiple processes, but the processes are still event dependent.


# fanout
Parallel Gateway

A parallel gateway is very different than the previous gateways because you aren't evaluating any condition or event. Instead, parallel gateways are used to represent two concurrent tasks in a business flow.

## topic
Inclusive Gateway

An inclusive gateway breaks the process flow into one or more flows. An example of a inclusive gateway is business actions taken based on survey results. In the example below, one process is triggered if the consumer is satisfied with product A. Another flow is triggered when the consumer indicates that they are satisfied with product B. A third process is triggered if they aren't satisfied with A. There will be a minimal flow of one and a max of two.

## direct
Event-Based Gateway

An event-based gateway is similar to a exclusive gateway because both involve one path in the flow. In the case of an event-based gateway, however, you are evaluating which event has occurred, not which condition is being met.

## queue
Exclusive Gateway

An exclusive gateway evaluates the state of the business process and—based on the condition—breaks the flow into one of the two or more mutually exclusive paths. Remember that the exclusive in "exclusive gateway" stands for mutually exclusive. In the example below, an exclusive gateway requires that the mode of transportation be evaluated. In this case, one light will be placed in the Old North Church if the British attack by land; two if by sea.

# More info

http://activiti.org/userguide/#bpmnInclusiveGateway
https://www.lucidchart.com/pages/bpmn/gateways
https://www.lucidchart.com/pages/bpmn-symbols-explained
https://camunda.org/bpmn/reference/
