tmpfile="$RANDOM-$(date +%s%N)"

cp ../app/handlers/upload/__index__/video/video_updating.png $3
cp ../app/handlers/upload/__index__/video/video_updating.webm $2

/home/openvk38/ffmpeg/ffmpeg -i $1 -ss 5 -vframes 1 -y $3
/home/openvk38/ffmpeg/ffmpeg -i $1 -c:v libvpx -crf 10 -b:v 1M -c:a libvorbis -vf scale=640x360,setsar=1:1 -y "/tmp/ffmOi$tmpfile.webm"

rm -f $2
cp "/tmp/ffmOi$tmpfile.webm" $2 
rm -f "/tmp/ffmOi$tmpfile.webm"
