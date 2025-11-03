function resetar() {
    if (confirm("Deseja reiniciar sua pontuação e nível?")) {
        fetch('reset.php')
            .then(() => location.reload());
    }
}
