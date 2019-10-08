# simple_scheduler
Simple scheduler for choosing a unique timeslot per person

# Setting up
1. Install docker
2. Make go.sh executable (in Linux use `chmod +x go.sh`)
3. Run go.sh (in Linux use `./go.sh`)

# Use
1. First create a new scheduler by giving it a unique name (e.g. meeting) and possible time slots separated by a new line: http://URL/doodle.php?action=setup
2. The new scheduler is available at: http://URL/doodle.php?action=doodle&name=meeting
3. To see the list of responses go to: http://URL/doodle.php?action=show&name=meeting
