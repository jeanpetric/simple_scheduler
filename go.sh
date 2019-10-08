docker stop doodle
docker rm doodle
docker build -t doodle .
docker run -d -p 80:80 --name doodle doodle
