FROM python:3.8

RUN apt-get update && apt-get clean

RUN pip install Flask==3.0.0

RUN git clone https://github.com/hanze-hbo-ict/itvb23ows-hive-ai.git

WORKDIR /itvb23ows-hive-ai

EXPOSE 7331

CMD ["flask", "--app", "app", "run", "--host", "0.0.0.0", "--debug"]
